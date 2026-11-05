<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\PaymentTransaction;
use App\Models\Stock;
use App\Models\StockBatch;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of pending orders.
     */
    public function pendingOrders()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->where('order_status', 'pending')
            ->allowedSorts([
                'order_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('orders.*');
                })
            ])
            ->with(['customer', 'outlet'])
            ->paginate($row);

        return view('orders.pending-orders', [
            'orders' => $orders
        ]);
    }

    /**
     * Display a listing of complete orders.
     */
    public function completeOrders()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->where('order_status', 'complete')
            ->allowedSorts([
                'order_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('orders.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('orders.complete-orders', [
            'orders' => $orders
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function storeOrder(StoreOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $invoice_no = IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-'
            ]);

            $items = $request->input('items', []);
            $outletId = $request->input('outlet_id'); // Frontend theke outlet_id asbe
            
            // সাবটোটাল, ডিসকাউন্ট এবং নেট অ্যামাউন্ট হিসাব করা
            $sub_total = 0;
            foreach ($items as $item) {
                $sub_total += $item['price'] * $item['qty'];
            }

            $discount = (float) $request->input('discount', 0);
            $vat = 0; 
            $net_payable = ($sub_total - $discount) + $vat;
            
            $pay_amount = (float) $request->input('pay_amount', 0);
            $due_amount = $net_payable - $pay_amount;
            if ($due_amount < 0) {
                $due_amount = 0;
            }

            // 3. Order Master Table-এ ডেটা সেভ করা (Status: pending)
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'outlet_id' => $outletId, // Order-er sathe outlet map rakha bhalo
                'invoice_no' => $invoice_no,
                'order_date' => Carbon::now(),
                'order_status' => 'pending', // Order pending rakha hocche
                'total_products' => count($items),
                'sub_total' => $sub_total,
                'discount' => $discount,
                'vat' => $vat,
                'total' => $net_payable,
                'payment_type' => $request->payment_type,
                'payment_reference' => $request->payment_reference ?? null,
                'pay_amount' => $pay_amount,
                'due_amount' => $due_amount,
            ]);

            // 4. Order Details save kora & Stocks-er booking_qty update kora
            foreach ($items as $item) {
                $unitPrice = (float) $item['price'];
                $quantity = (float) $item['qty'];
                
                OrderDetails::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'stock_id'    => $item['stock_id'],
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'total'       => $unitPrice * $quantity,
                ]);

                // Main stocks table theke booking_qty barie dewa (Reserved for pending order)
                $stock = Stock::where('product_id', $item['product_id'])
                            ->where('outlet_id', $outletId)
                            ->first();

                if ($stock) {
                    // Check if available stock (quantity - booking_qty) is sufficient
                    $availableStock = $stock->quantity - $stock->booking_qty;
                    if ($quantity > $availableStock) {
                        throw new \Exception("Stock is insufficient for product ID: {$item['product_id']}");
                    }

                    // Increment booking qty
                    $stock->increment('booking_qty', $quantity);
                } else {
                    throw new \Exception("Stock record not found for product ID: {$item['product_id']}");
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully as Pending!',
                    'invoice_url' => route('order.invoiceDownload', $order->id),
                ]);
            }

            return Redirect::route('order.invoiceDownload', $order->id)->with('success', 'Order has been created as Pending!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order Store Error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function orderDetails(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
                        ->where('order_id', $order_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('orders.details-order', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(Request $request)
    {

        $order_id = $request->id;

        DB::beginTransaction();

        try {
            // 1. Order ebong tar details (sathe outlet_id jate thake) fetch kora
            $order = Order::with('details')->findOrFail($order_id);

            // Jodi order already complete ba cancel thake
            if ($order->order_status !== 'pending') {
                throw new \Exception("This order has already been processed or completed!");
            }

            foreach ($order->details as $detail) {
                $qtyToReduce = (float) $detail->quantity;

                // 2. Main stock table theke booking_qty release kora ebong actual quantity minus kora
                $stock = Stock::where('product_id', $detail->product_id)
                            ->where('outlet_id', $order->outlet_id)
                            ->first();

                if (!$stock) {
                    throw new \Exception("Stock record not found for product ID: {$detail->product_id} in this outlet.");
                }

                // Check if booking quantity is sufficient or decrease safely
                if ($stock->booking_qty < $qtyToReduce) {
                    // Fallback safety if booking wasn't recorded properly, but normally it should match
                    Log::warning("Booking qty mismatch for product ID: {$detail->product_id} in outlet ID: {$order->outlet_id}");
                }

                // Booking qty release and actual stock reduce
                $stock->decrement('booking_qty', $qtyToReduce);
                $stock->decrement('quantity', $qtyToReduce);

                // 3. FIFO (First In, First Out) Batch Deduction from stock_batch table
                $batches = StockBatch::where('stock_id', $stock->id)
                                    ->where('remain_qty', '>', 0)
                                    ->orderBy('created_at', 'ASC') // Purono batch age sesh hobe
                                    ->get();

                $remainingQtyToDeduced = $qtyToReduce;

                foreach ($batches as $batch) {
                    if ($remainingQtyToDeduced <= 0) break;

                    if ($batch->remain_qty >= $remainingQtyToDeduced) {
                        $batch->decrement('remain_qty', $remainingQtyToDeduced);
                        $remainingQtyToDeduced = 0;
                    } else {
                        $remainingQtyToDeduced -= $batch->remain_qty;
                        $batch->update(['remain_qty' => 0]);
                    }
                }

                // Jodi batch-er total quantity stock-er cheye kom thake ebong unfulfilled thake
                if ($remainingQtyToDeduced > 0) {
                    Log::warning("Batch remain quantity was insufficient for product ID: {$detail->product_id}. Unfulfilled qty: {$remainingQtyToDeduced}");
                }
            }

            // 4. Order status complete update kora
            $order->update(['order_status' => 'complete']);

            PaymentTransaction::create([
                'order_id'    => $order->id,
                'customer_id' => $order->customer_id,
                'payment' => $order->pay_amount,
                'payment_date' => Carbon::now(),
            ]);

            // Sob thik thakle commit
            DB::commit();

            return Redirect::route('order.pendingOrders')->with('success', 'Order has been completed, payment and inventory adjusted successfully!');

        } catch (\Exception $e) {
            // Kono error hole rollback kore dibe
            DB::rollBack();

            // Error log record rakha
            Log::error('Order Completion Error (ID: ' . $order_id . '): ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to complete order! Error: ' . $e->getMessage());
        }
    }

    public function invoiceDownload(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('pos.print-receipt-2', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    public function printReceipt(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
                        ->where('order_id', $order_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('pos.print-receipt-2', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    public function pendingDue()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->where('due_amount', '>', 0)
            ->allowedSorts([
                'order_date',
                'due_amount',
                'pay_amount',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')->orderBy('customers.name', $descending ? 'DESC' : 'ASC')->select('orders.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('orders.pending-due', [
            'orders' => $orders
        ]);
    }

    public function orderDueAjax(int $id)
    {
        $order = Order::findOrFail($id);

        return response()->json($order);
    }

    public function updateDue(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        
        $request->validate([
            'customer_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'due_amount' => 'required|numeric|min:0.01|max:' . $order->due_amount,
        ]);

        $payAmount = (float) $request->due_amount;

        DB::beginTransaction();

        try {

            $order->pay_amount += $payAmount;
            $order->due_amount -= $payAmount;
            $order->save();

            DB::commit();

            PaymentTransaction::create([
                'order_id'    => $order->id,
                'customer_id' => $order->customer_id,
                'payment' => $payAmount,
                'payment_date' => Carbon::now(),
            ]);

            return redirect()->back()->with('success', 'Due payment updated successfully for invoice ' . $order->invoice_no . '!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update due payment: ' . $e->getMessage());
        }
    }
}
