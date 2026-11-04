@extends('dashboard.body.main')

@section('container')
    <style>
        .row-selector-container {
            min-width: 0; /* Default for mobile - allows shrinking */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        @media (min-width: 576px) {
            .row-selector-container {
                min-width: 180px; /* Apply min-width only on sm+ screens */
                padding-top: 0;
                padding-bottom: 0;
            }
        }
    </style>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert text-white bg-danger" role="alert">
                    <div class="iq-alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Stock List</h4>
                    <p class="mb-0">Outletwise inventory calculation. </p>
                </div>
                <!-- begin: Action Buttons -->
                <div class="d-flex align-items-center">
                    <a href="{{ route('stocks.create') }}" class="btn btn-primary add-list d-flex align-items-center">
                        <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Stock
                    </a>
                </div>
                <!-- end: Action Buttons -->
            </div>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('stocks.index') }}" method="get" class="search-form">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="form-group mb-0 mr-2 mt-n3 row-selector-container">
                        <div class="d-flex align-items-center">
                            <label for="row" class="mb-0 mr-2" style="min-width: 50px;">Row:</label>
                            <select class="form-control" name="row">
                                <option value="10" @if(request('row') == '10')selected="selected"@endif>10</option>
                                <option value="25" @if(request('row') == '25')selected="selected"@endif>25</option>
                                <option value="50" @if(request('row') == '50')selected="selected"@endif>50</option>
                                <option value="100" @if(request('row') == '100')selected="selected"@endif>100</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-sm-3 align-self-center" for="search">Search:</label>
                        <div class="input-group col-sm-8">
                            <input type="text" id="search" class="form-control" name="filter[search]" placeholder="Search stock" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text bg-primary">
                                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                </button>
                                <a href="{{ route('stocks.index') }}" class="input-group-text bg-danger">
                                    <x-heroicon-o-x-mark class="w-5 h-5" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-12">
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>SL</th>
                            <th>Product</th>
                            <th>Outlet</th>
                            <th>Quantity</th>
                            <!-- <th>Purchase Rate</th> -->
                            <th>Selling Rate</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @forelse ($stocks as $index => $stock)
                            <tr>
                                <td>{{ $index ? ++$index : 1 }}</td>
                                <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                <td>{{ $stock->outlet->name ?? 'N/A' }}</td>
                                <td>{{ number_format($stock->quantity, 2) }}</td>
                                <!-- <td>{{ number_format($stock->purchase_rate, 2) }}</td> -->
                                <td>{{ number_format($stock->selling_rate, 2) }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center list-action justify-content-center">
                                        <!-- View Batches Button / Action -->
                                        <a class="badge bg-success mr-2" data-toggle="tooltip" data-placement="top" title="View Batches" href="#">
                                            <x-heroicon-o-eye class="w-5 h-5"/>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No stock records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-3">
                {{ $stocks->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
