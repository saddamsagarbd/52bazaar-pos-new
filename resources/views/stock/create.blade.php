@extends('dashboard.body.main')

@section('container')
<style>
    .card, .card-body {
        overflow: visible !important;
    }
    .table-responsive {
        overflow-x: visible !important;
        overflow-y: visible !important;
    }
    
    /* Force the dropdown to show ABOVE the input field */
    .search-results {
        background-color: #ffffff !important;
        border: 1px solid rgba(0, 0, 0, 0.15);
        display: none;
        position: absolute;
        bottom: 100% !important; /* Opens upwards */
        top: auto !important;    /* Clears top positioning */
        left: 0;
        z-index: 999999 !important;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 -0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if ($errors->any())
                <div class="alert text-white bg-danger" role="alert">
                    <div class="iq-alert-text">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <x-heroicon-o-x-mark class="w-6 h-6"/>
                    </button>
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Batch Stock Entry</h4>
                    <p class="mb-0">Add multiple products or batches with specific purchase and selling rates.</p>
                </div>
                <div>
                    <a href="{{ route('stocks.index') }}" class="btn btn-secondary add-list d-flex align-items-center">
                        <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" /> Back to Stock
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('stocks.store') }}" method="POST" id="stock-entry-form">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table table-bordered mb-3" id="stock-entry-table">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Product</th>
                                        <th style="width: 16%;">Outlet</th>
                                        <th style="width: 8%;">Quantity</th>
                                        <th style="width: 11%;">Purchase Rate</th>
                                        <th style="width: 11%;">Selling Rate</th>
                                        <th style="width: 12%;">Expire Date</th>
                                        <th style="width: 12%;">Purchase Date</th>
                                        <th style="width: 10%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="stock-rows-container">
                                    <!-- Initial Row -->
                                    <tr class="stock-row">
                                        <td>
                                            <div class="position-relative">
                                                <input type="text" class="form-control product-search" placeholder="Type product name..." autocomplete="off" required>
                                                <input type="hidden" name="items[0][product_id]" class="product-id-input" required>
                                                <div class="search-results product-results dropdown-menu shadow w-100"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="position-relative">
                                                <input type="text" class="form-control outlet-search" placeholder="Type outlet name..." autocomplete="off" required>
                                                <input type="hidden" name="items[0][outlet_id]" class="outlet-id-input" required>
                                                <div class="search-results outlet-results dropdown-menu shadow w-100"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][purchase_rate]" class="form-control purchase-rate" placeholder="0.00" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][selling_rate]" class="form-control selling-rate" placeholder="0.00" required>
                                            <div class="invalid-feedback rate-error-msg" style="display: none; font-size: 11px;">
                                                Selling rate cannot be less than purchase rate!
                                            </div>
                                        </td>
                                        <td>
                                            <input type="date" name="items[0][expire_date]" class="form-control expire-date">
                                            <div class="invalid-feedback expire-date-error-msg" style="display: none; font-size: 11px;">
                                                Expire date can not be past date!
                                            </div>
                                        </td>
                                        <td>
                                            <input type="date" name="items[0][purchase_date]" class="form-control purchase-date">
                                            <div class="invalid-feedback purchase-date-error-msg" style="display: none; font-size: 11px;">
                                                Purchase date can not be future date!
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" id="add-row-btn" class="btn btn-success d-flex align-items-center">
                                <x-heroicon-o-plus class="w-5 h-5 mr-1" /> Add Another Row
                            </button>
                            <button type="submit" class="btn btn-primary d-flex align-items-center">
                                <x-heroicon-o-check class="w-5 h-5 mr-1" /> Save All Batches
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AJAX Live Search & Dynamic Row Script -->
<script>
    let rowIndex = 0;

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Add Row Handler
    document.getElementById('add-row-btn').addEventListener('click', function () {
        rowIndex++;
        const container = document.getElementById('stock-rows-container');
        
        const newRow = document.createElement('tr');
        newRow.classList.add('stock-row');
        newRow.innerHTML = `
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control product-search" placeholder="Type product name..." autocomplete="off" required>
                    <input type="hidden" name="items[${rowIndex}][product_id]" class="product-id-input" required>
                    <div class="search-results product-results dropdown-menu shadow w-100"></div>
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control outlet-search" placeholder="Type outlet name..." autocomplete="off" required>
                    <input type="hidden" name="items[${rowIndex}][outlet_id]" class="outlet-id-input" required>
                    <div class="search-results outlet-results dropdown-menu shadow w-100"></div>
                </div>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control" placeholder="Qty" min="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][purchase_rate]" class="form-control purchase-rate" placeholder="0.00" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][selling_rate]" class="form-control selling-rate" placeholder="0.00" required>
                <div class="invalid-feedback rate-error-msg" style="display: none; font-size: 11px;">
                    Selling rate cannot be less than purchase rate!
                </div>
            </td>
            <td>
                <input type="date" name="items[${rowIndex}][expire_date]" class="form-control expire-date">
                <div class="invalid-feedback expire-date-error-msg" style="display: none; font-size: 11px;">
                    Expire date can not be past date!
                </div>
            </td>
            <td>
                <input type="date" name="items[${rowIndex}][purchase_date]" class="form-control purchase-date">
                <div class="invalid-feedback purchase-date-error-msg" style="display: none; font-size: 11px;">
                    Purchase date can not be future date!
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
                </button>
            </td>
        `;
        
        container.appendChild(newRow);
        updateRemoveButtons();
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
        }
        if (!e.target.closest('.position-relative')) {
            document.querySelectorAll('.search-results').forEach(el => el.style.display = 'none');
        }
    });

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.stock-row');
        rows.forEach((row) => {
            const removeBtn = row.querySelector('.remove-row');
            removeBtn.disabled = rows.length === 1;
        });
    }

    // Real-time validation check on input for purchase and selling rates
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('purchase-rate') || e.target.classList.contains('selling-rate')) {
            const row = e.target.closest('tr');
            const purchaseInput = row.querySelector('.purchase-rate');
            const sellingInput = row.querySelector('.selling-rate');
            const errorMsg = row.querySelector('.rate-error-msg');

            const purchaseVal = parseFloat(purchaseInput.value) || 0;
            const sellingVal = parseFloat(sellingInput.value) || 0;

            if (sellingVal > 0 && sellingVal < purchaseVal) {
                sellingInput.classList.add('is-invalid');
                errorMsg.style.display = 'block';
            } else {
                sellingInput.classList.remove('is-invalid');
                errorMsg.style.display = 'none';
            }
        }

        if (e.target.classList.contains('purchase-date')) {
            const row = e.target.closest('tr');
            const pDateInput = row.querySelector('.purchase-date');
            const errorPdateMsg = row.querySelector('.purchase-date-error-msg');

            if (pDateInput && pDateInput.value) {
                const purchaseDate = new Date(pDateInput.value);
                purchaseDate.setHours(0, 0, 0, 0);

                // Rule: Purchase date cannot be in the future
                if (purchaseDate > today) {
                    isValid = false;
                    pDateInput.classList.add('is-invalid');
                    if (errorPdateMsg) {
                        errorPdateMsg.textContent = "Purchase date cannot be in the future!";
                        errorPdateMsg.style.display = 'block';
                    }
                } else {
                    pDateInput.classList.remove('is-invalid');
                    if (errorPdateMsg) {
                        errorPdateMsg.style.display = 'none';
                    }
                }
            }

        }

        if (e.target.classList.contains('expire-date')) {
            const row = e.target.closest('tr');
            const exDateInput = row.querySelector('.expire-date');
            const errorExdateMsg = row.querySelector('.expire-date-error-msg');

            if (exDateInput && exDateInput.value) {
                const expireDate = new Date(exDateInput.value);
                expireDate.setHours(0, 0, 0, 0);

                // Rule: Purchase date cannot be in the future
                if (expireDate < today) {
                    isValid = false;
                    exDateInput.classList.add('is-invalid');
                    if (errorExdateMsg) {
                        errorExdateMsg.textContent = "Expire date cannot be past date!";
                        errorExdateMsg.style.display = 'block';
                    }
                } else {
                    exDateInput.classList.remove('is-invalid');
                    if (errorExdateMsg) {
                        errorExdateMsg.style.display = 'none';
                    }
                }
            }

        }
    });

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Form submit validation safeguard
    document.getElementById('stock-entry-form').addEventListener('submit', function (e) {
        const rows = document.querySelectorAll('.stock-row');
        let isValid = true;

        rows.forEach((row, index) => {
            const purchaseInput = row.querySelector('.purchase-rate');
            const sellingInput = row.querySelector('.selling-rate');
            const errorMsg = row.querySelector('.rate-error-msg');

            const pDateInput = row.querySelector('.purchase-date');
            const errorPdateMsg = row.querySelector('.purchase-date-error-msg');

            const exDateInput = row.querySelector('.expire-date');
            const errorExdateMsg = row.querySelector('.expire-date-error-msg');

            const purchaseVal = parseFloat(purchaseInput.value) || 0;
            const sellingVal = parseFloat(sellingInput.value) || 0;

            if (sellingVal < purchaseVal) {
                isValid = false;
                sellingInput.classList.add('is-invalid');
                errorMsg.style.display = 'block';
            }

            if (pDateInput && pDateInput.value) {
                const purchaseDate = new Date(pDateInput.value);
                purchaseDate.setHours(0, 0, 0, 0);

                // Rule: Purchase date cannot be in the future
                if (purchaseDate > today) {
                    isValid = false;
                    pDateInput.classList.add('is-invalid');
                    if (errorPdateMsg) {
                        errorPdateMsg.textContent = "Purchase date cannot be in the future!";
                        errorPdateMsg.style.display = 'block';
                    }
                } else {
                    pDateInput.classList.remove('is-invalid');
                    if (errorPdateMsg) {
                        errorPdateMsg.style.display = 'none';
                    }
                }
            }

            if (exDateInput && exDateInput.value) {
                const expireDate = new Date(exDateInput.value);
                expireDate.setHours(0, 0, 0, 0);

                // Rule: Purchase date cannot be in the future
                if (expireDate < today) {
                    isValid = false;
                    exDateInput.classList.add('is-invalid');
                    if (errorExdateMsg) {
                        errorExdateMsg.textContent = "Expire date cannot be past date!";
                        errorExdateMsg.style.display = 'block';
                    }
                } else {
                    exDateInput.classList.remove('is-invalid');
                    if (errorExdateMsg) {
                        errorExdateMsg.style.display = 'none';
                    }
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            // alert('Please fix the selling rate errors before submitting. Selling rate cannot be less than purchase rate.');
        }
    });

    // Product Live Search
    document.addEventListener('input', debounce(function (e) {
        if (e.target.classList.contains('product-search')) {
            const query = e.target.value;
            const containerDiv = e.target.closest('.position-relative');
            const resultsBox = containerDiv.querySelector('.product-results');
            const idInput = containerDiv.querySelector('.product-id-input');

            idInput.value = '';

            if (query.length < 2) {
                resultsBox.style.display = 'none';
                return;
            }

            fetch(`{{ route('api.products.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsBox.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(product => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'dropdown-item';
                            item.textContent = product.name;
                            item.addEventListener('click', function (evt) {
                                evt.preventDefault();
                                e.target.value = product.name;
                                idInput.value = product.id;
                                resultsBox.style.display = 'none';
                            });
                            resultsBox.appendChild(item);
                        });
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<span class="dropdown-item text-muted">No products found</span>';
                        resultsBox.style.display = 'block';
                    }
                });
        }

        // Outlet Live Search
        if (e.target.classList.contains('outlet-search')) {
            const query = e.target.value;
            const containerDiv = e.target.closest('.position-relative');
            const resultsBox = containerDiv.querySelector('.outlet-results');
            const idInput = containerDiv.querySelector('.outlet-id-input');

            idInput.value = '';

            if (query.length < 1) {
                resultsBox.style.displayN = 'none';
                return;
            }

            fetch(`{{ route('api.outlets.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsBox.innerHTML = '';
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(outlet => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'dropdown-item';
                            item.textContent = outlet.name;
                            item.addEventListener('click', function (evt) {
                                evt.preventDefault();
                                e.target.value = outlet.name;
                                idInput.value = outlet.id;
                                resultsBox.style.display = 'none';
                            });
                            resultsBox.appendChild(item);
                        });
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<span class="dropdown-item text-muted">No outlets found</span>';
                        resultsBox.style.display = 'block';
                    }
                });
        }
    }, 300));
</script>
@endsection