<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $outlets = QueryBuilder::for(Outlet::class)
                ->allowedSorts([
                    'code',
                    'name',
                    AllowedSort::callback('parent.name', function ($query, $descending) {
                        $query->join('outlets as p', 'p.id', '=', 'outlets.parent_id')
                            ->orderBy('p.code', $descending ? 'DESC' : 'ASC')
                            ->select('outlets.*');
                    })
                ])
                ->allowedFilters([
                    'name', 
                    'code',
                    // রিলেশনের নাম দিয়ে ফিল্টার করতে চাইলে:
                    AllowedFilter::callback('parent_name', function ($query, $value) {
                        $query->whereHas('parent', function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%");
                        });
                    })
                ])
                ->with(['parent'])
                ->paginate($row)
                ->appends(request()->query());
            
        return view('outlet.index', [
            'outlets' => $outlets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $outlets = Outlet::where('status', true)->select('name', 'code', 'id')->get();

        $compactData = compact('outlets');

        return view('outlet.create', $compactData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
