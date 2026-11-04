<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockEntryRequest;
use App\Models\Stock;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $row = $request->get('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $stocks = QueryBuilder::for(Stock::class)
            ->allowedSorts([
                'quantity',
                'purchase_rate',
                'selling_rate',
                AllowedSort::callback('product.name', function ($query, $descending) {
                    $query->join('products', 'stocks.product_id', '=', 'products.id')
                        ->orderBy('products.name', $descending ? 'DESC' : 'ASC')
                        ->select('stocks.*');
                }),
                AllowedSort::callback('outlet.name', function ($query, $descending) {
                    $query->join('outlets', 'stocks.outlet_id', '=', 'outlets.id')
                        ->orderBy('outlets.name', $descending ? 'DESC' : 'ASC')
                        ->select('stocks.*');
                }),
            ])
            ->allowedFilters([
                AllowedFilter::partial('product.name', 'product.name'),
                AllowedFilter::partial('outlet.name', 'outlet.name'),
                
                // Global search box-er jonno callback filter:
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->whereHas('product', function ($subQuery) use ($value) {
                            $subQuery->where('name', 'like', "%{$value}%");
                        })
                        ->orWhereHas('outlet', function ($subQuery) use ($value) {
                            $subQuery->where('name', 'like', "%{$value}%");
                        });
                    });
                }),
            ])
            ->with(['product', 'outlet'])
            ->latest()
            ->paginate($row)
            ->appends(request()->query());

        $compactData = compact('stocks');

        return view('stock.index', $compactData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stock.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockEntryRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            foreach ($validatedData['items'] as $item) {
                
                // 1. Find or create main stock summary for this product & outlet
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item['product_id'],
                        'outlet_id'  => $item['outlet_id'],
                    ],
                    [
                        'purchase_rate' => $item['purchase_rate'],
                        'selling_rate'  => $item['selling_rate'],
                    ]
                );

                // Update main stock totals & latest rates
                $stock->increment('quantity', $item['quantity']);
                
                $stock->update([
                    'purchase_rate' => $item['purchase_rate'],
                    'selling_rate'  => $item['selling_rate'],
                ]);

                // 2. Create a unique Batch for Profit & Loss tracking
                StockBatch::create([
                    'batch_id'          => 'BATCH-' . strtoupper(Str::random(8)), // Unique batch code
                    'stock_id'          => $stock->id,
                    'quantity'          => $item['quantity'],
                    'remain_qty'        => $item['quantity'],
                    'purchase_rate'     => $item['purchase_rate'],
                    'selling_rate'      => $item['selling_rate'],
                    'expire_date'       => $item['expire_date'],
                    'purchase_date'     => $item['purchase_date'],
                ]);
            }

            // Jodi sobkichu thik thake tahale commit hobe
            DB::commit();

            return redirect()->route('stocks.index')->with('success', 'Batch stocks and batches added successfully!');

        } catch (\Exception $e) {
            // Kono error hole database rollback kore dibe
            DB::rollBack();

            // Error log-e save kore rakha jate debug korte subidha hoy
            Log::error('Stock Entry Error: ' . $e->getMessage());

            // User ke error message sahitye back kore dewa
            return redirect()->back()->withInput()->with('error', 'Something went wrong! Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
