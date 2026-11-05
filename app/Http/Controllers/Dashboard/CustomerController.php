<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Customer;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $customers = QueryBuilder::for(Customer::class)
            ->allowedSorts(['name', 'email', 'phone', 'city'])
            ->allowedFilters(['name', 'city'])
            ->filter(request(['search']))
            ->withCount('orders') 
            ->withSum('orders as total_payment', 'pay_amount')
            ->withSum('orders as total_due', 'due_amount')
            ->paginate(request('row', 10)) // Global helper function
            ->appends(request()->query());

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return Redirect::route('customers.index')->with('success', 'Customer has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['orders' => function($query) {
            $query->latest();
        }]);
        
        return view('customers.show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return Redirect::route('customers.index')->with('success', 'Customer has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return Redirect::route('customers.index')->with('success', 'Customer has been deleted!');
    }

    public function payDue(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payAmount = (float) $request->amount;

        DB::beginTransaction();
        try {
            // Find orders of this customer that have due amount, starting from oldest (FIFO style)
            $orders = Order::where('customer_id', $customer->id)
                        ->where('due_amount', '>', 0)
                        ->orderBy('created_at', 'ASC')
                        ->get();

            $remainingPay = $payAmount;

            foreach ($orders as $order) {
                if ($remainingPay <= 0) break;

                $currentDue = $order->due_amount;

                if ($order->due_amount >= $remainingPay) {
                    $appliedPayment = $remainingPay;
                    // Partial or full payment for this specific order
                    $order->due_amount -= $remainingPay;
                    $order->pay_amount += $remainingPay;
                    $remainingPay = 0;
                } else {
                    $appliedPayment = $currentDue;
                    // Order due is less than payment, clear this order due and move to next
                    $remainingPay -= $order->due_amount;
                    $order->pay_amount += $order->due_amount;
                    $order->due_amount = 0;
                }

                $order->save();

                PaymentTransaction::create([
                    'order_id'    => $order->id,
                    'customer_id' => $order->customer_id,
                    'payment' => $appliedPayment,
                    'payment_date' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Due payment collected successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 422);
        }
    }
}
