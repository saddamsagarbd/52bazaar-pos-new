@extends('dashboard.body.main')

@section('container')
<style>
    /* ===== POS Terminal: unified sizing & alignment ===== */
    .pos-terminal { --field-h: 44px; --radius: 8px; }

    .pos-terminal .pos-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: #4a5568;
        margin-bottom: 6px;
        line-height: 1.2;
    }

    /* All main fields share the same height */
    .pos-terminal .form-control:not(.form-control-sm):not(.form-control-lg),
    .pos-terminal .input-group .btn,
    .pos-terminal .input-group-text {
        height: var(--field-h);
    }
    .pos-terminal .form-control { border-radius: var(--radius); }
    .pos-terminal .input-group > .form-control { border-top-left-radius: 0; border-bottom-left-radius: 0; }
    .pos-terminal .input-group > .input-group-prepend > .input-group-text {
        border-top-left-radius: var(--radius); border-bottom-left-radius: var(--radius);
        display: flex; align-items: center; justify-content: center; min-width: 46px;
    }
    .pos-terminal .input-group > .input-group-append > .btn {
        border-top-right-radius: var(--radius); border-bottom-right-radius: var(--radius);
        display: flex; align-items: center; justify-content: center; min-width: 46px; padding: 0 12px;
    }

    /* Select2 aligned with Bootstrap inputs */
    .pos-terminal .select2-container { width: 100% !important; }
    .pos-terminal .input-group > .select2-wrapper { flex: 1 1 auto; width: 1%; min-width: 0; }
    .pos-terminal .select2-container--default .select2-selection--single {
        height: var(--field-h);
        border: 1px solid #ced4da;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
    }
    .pos-terminal .input-group .select2-container--default .select2-selection--single {
        border-top-right-radius: 0; border-bottom-right-radius: 0;
    }
    .pos-terminal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal; padding-left: 12px; padding-right: 30px; width: 100%;
    }
    .pos-terminal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: var(--field-h); right: 6px;
    }
    .pos-terminal .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 8px; line-height: 1;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #4c7bf4;
    }

    /* Search dropdown */
    .pos-terminal .search-wrap { position: relative; }
    .pos-terminal #search_results_dropdown {
        top: 100%; left: 0; margin-top: 4px; padding: 4px 0;
        border-radius: var(--radius); border: 1px solid #e2e8f0; z-index: 1050;
    }

    /* Table */
    .pos-terminal #pos-table { border-collapse: separate; border-spacing: 0; }
    .pos-terminal #pos-table thead th {
        position: sticky; top: 0; z-index: 2;
        background: #f1f5f9; font-size: 12px; text-transform: uppercase; letter-spacing: .4px;
        color: #475569; vertical-align: middle; padding: 12px 10px; border-bottom: 2px solid #e2e8f0;
    }
    .pos-terminal #pos-table tbody td { vertical-align: middle; padding: 10px; }
    .pos-terminal #pos-table .form-control-sm { height: 36px; border-radius: 6px; }
    .pos-terminal #pos-table .btn-sm {
        width: 34px; height: 34px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
    }

    /* Summary rows: equal height */
    .pos-terminal .summary-row {
        display: flex; align-items: center; justify-content: space-between;
        min-height: 44px; margin-bottom: 8px;
    }
    .pos-terminal .summary-row .summary-input { width: 160px; height: 38px; }
    .pos-terminal .summary-total {
        background: #fff; border: 1px dashed #cbd5e1; border-radius: var(--radius);
        padding: 8px 14px; min-height: 56px;
    }
    .pos-terminal .summary-panel { border-radius: 10px; }

    /* Payment toggle */
    .pos-terminal .btn-group-toggle .btn {
        height: var(--field-h); display: flex; align-items: center; justify-content: center;
        font-weight: 600; flex: 1 1 0;
    }
    .pos-terminal .btn-group-toggle .btn:first-child { border-top-left-radius: var(--radius); border-bottom-left-radius: var(--radius); }
    .pos-terminal .btn-group-toggle .btn:last-child { border-top-right-radius: var(--radius); border-bottom-right-radius: var(--radius); }

    .pos-terminal .paid-input { height: 52px; font-size: 1.15rem; }
    .pos-terminal .checkout-btn { height: 56px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; }

    @media (max-width: 767.98px) {
        .pos-terminal .summary-panel .border-right { border-right: 0 !important; }
    }
</style>

<div class="container-fluid pos-terminal">
    <!-- Success Alert -->
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success alert-dismissible fade show" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- MAIN POS CONTAINER -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3 px-4">
                    <h4 class="mb-0 text-white font-weight-bold d-flex align-items-center">
                        <x-heroicon-o-shopping-cart class="w-6 h-6 mr-2" /> POS Billing Terminal
                    </h4>
                    <span class="badge badge-light text-primary font-weight-bold px-3 py-2" id="cart-item-count">0 Items</span>
                </div>

                <div class="card-body p-4">
                    <!-- TOP BAR -->
                    <div class="row mb-4">
                        <!-- Outlet -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="pos-label">Select Outlet <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="outlet_id" name="outlet_id" onchange="onOutletChange()">
                                <option value="" selected disabled>-- Select Outlet --</option>
                                @foreach($outlets ?? [] as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product search -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="pos-label">Search Product (from Stock)</label>
                            <div class="search-wrap">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary text-white border-primary">
                                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                        </span>
                                    </div>
                                    <input type="text" id="pos_product_search" class="form-control border-primary"
                                        placeholder="Type product name/code..." autocomplete="off" disabled>
                                </div>
                                <div id="search_results_dropdown" class="dropdown-menu w-100 shadow-lg" style="max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="col-md-4">
                            <label class="pos-label">Customer</label>
                            <div class="input-group flex-nowrap">
                                <div class="select2-wrapper">
                                    <select class="form-control select2" id="customer_id" name="customer_id">
                                        <option value="" selected disabled>-- Search Customer --</option>
                                    </select>
                                </div>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" title="Add Customer" data-toggle="modal" data-target="#addCustomerModal">
                                        <x-heroicon-o-plus class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POS ITEMS TABLE -->
                    <div class="table-responsive mb-4 border rounded" style="min-height: 250px; max-height: 450px; overflow-y: auto;">
                        <table class="table table-hover text-center mb-0" id="pos-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 25%;" class="text-left">Product Name</th>
                                    <th style="width: 15%;">Batch / Available</th>
                                    <th style="width: 15%;">Unit Price</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Sub Total</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="pos-table-body">
                                <tr id="empty-cart-row">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-secondary mb-2" />
                                        <p class="mb-0 font-weight-bold">Please select an outlet and search products to add items.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTTOM SUMMARY & PAYMENT PANEL -->
                    <div class="row mx-0 border bg-light summary-panel p-4">
                        <!-- Billing Summary -->
                        <div class="col-md-6 border-right pr-md-4 mb-4 mb-md-0">
                            <h5 class="font-weight-bold text-dark mb-3">Order Calculations</h5>

                            <div class="summary-row">
                                <span class="font-weight-bold text-muted">Sub Total:</span>
                                <span class="h5 font-weight-bold text-dark mb-0" id="summary-subtotal">0.00</span>
                            </div>

                            <div class="summary-row">
                                <span class="font-weight-bold text-muted">Discount (৳):</span>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right font-weight-bold summary-input"
                                    id="summary-discount" value="0.00" oninput="recalculateTotals()">
                            </div>

                            <div class="summary-row summary-total">
                                <span class="h5 font-weight-bold text-dark mb-0">Grand Total:</span>
                                <span class="h4 font-weight-bold text-primary mb-0" id="summary-grandtotal">0.00</span>
                            </div>

                            <div class="summary-row mt-3 mb-0">
                                <span class="font-weight-bold text-muted">Return/Change Amount:</span>
                                <span class="h5 font-weight-bold text-success mb-0" id="summary-change">0.00</span>
                            </div>
                        </div>

                        <!-- Payment -->
                        <div class="col-md-6 pl-md-4">
                            <h5 class="font-weight-bold text-dark mb-3">Payment Details</h5>

                            <div class="form-group mb-3">
                                <label class="pos-label">Select Payment Method</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active">
                                        <input type="radio" name="payment_type" id="pay_cash" value="Cash" checked onchange="togglePaymentFields()"> Cash
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="payment_type" id="pay_card" value="Card" onchange="togglePaymentFields()"> Card
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="payment_type" id="pay_mfs" value="MFS" onchange="togglePaymentFields()"> MFS (bKash/Nagad)
                                    </label>
                                </div>
                            </div>

                            <div class="form-group mb-3" id="payment_reference_group" style="display: none;">
                                <label class="pos-label" id="payment_ref_label">Transaction / Reference No.</label>
                                <input type="text" id="payment_reference" class="form-control" placeholder="Enter Transaction ID or Card Approval Code">
                            </div>

                            <div class="form-group mb-4">
                                <label class="pos-label">Paid Amount (৳)</label>
                                <input type="number" step="0.01" min="0" id="summary-paid-amount" class="form-control paid-input font-weight-bold text-success"
                                    placeholder="0.00" oninput="recalculateTotals()">
                            </div>

                            <button type="button" class="btn btn-primary btn-block shadow font-weight-bold checkout-btn" onclick="processOrderCheckout()">
                                <x-heroicon-o-check-circle class="w-6 h-6 mr-2" /> Complete &amp; Print Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg pos-terminal">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">Add New Customer</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form onsubmit="storeCustomer(event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="pos-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="pos-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="pos-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="pos-label">City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                        <div class="col-md-12 form-group mb-0">
                            <label class="pos-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" style="height:auto;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('specificpagescripts')
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let posCart = [];

    window.addEventListener('load', function () {
        // Initialize Customer Select2
        $('#customer_id').select2({
            placeholder: "-- Search Customer --",
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('pos.customers.search') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ term: params.term }),
                processResults: data => ({ results: data.results }),
                cache: true
            }
        });

        // Initialize Outlet Select2
        $('#outlet_id').select2({ width: '100%' });
    });

    // When Outlet changes, enable product search input and clear cart if outlet changes
    function onOutletChange() {
        let outletId = document.getElementById('outlet_id').value;
        let searchInput = document.getElementById('pos_product_search');

        if (outletId) {
            searchInput.disabled = false;
            searchInput.focus();
        } else {
            searchInput.disabled = true;
        }

        // Clear cart when switching outlets to prevent mixed outlet items
        posCart = [];
        renderTable();
    }

    // Live Search Input for Products from Stocks based on selected Outlet
    const searchInput = document.getElementById('pos_product_search');
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        let query = this.value.trim();
        let outletId = document.getElementById('outlet_id').value;

        if (!outletId) {
            alert('Please select an outlet first!');
            this.value = '';
            return;
        }

        if (query.length < 2) {
            document.getElementById('search_results_dropdown').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('pos.product.search') }}?outlet_id=${outletId}&term=${query}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                let dropdown = document.getElementById('search_results_dropdown');
                dropdown.innerHTML = '';

                if (data.stocks && data.stocks.length > 0) {
                    data.stocks.forEach(stock => {
                        const remainQty = parseFloat(stock.quantity) - parseFloat(stock.booking_qty)
                        let item = document.createElement('a');
                        item.className = 'dropdown-item d-flex justify-content-between align-items-center py-2 border-bottom';
                        item.href = 'javascript:void(0)';
                        item.innerHTML = `
                            <div>
                                <strong>${stock.product.name}</strong> <small class="text-muted">(${stock.product.code || 'N/A'})</small>
                            </div>
                            <span class="badge badge-primary ml-2">৳${stock.selling_rate} | Stock: ${remainQty}</span>
                        `;
                        item.onclick = function() {
                            appendProductToTable(stock);
                            searchInput.value = '';
                            dropdown.style.display = 'none';
                        };
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            });
        }, 300);
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-wrap')) {
            document.getElementById('search_results_dropdown').style.display = 'none';
        }
    });

    // Append Stock Item to Cart Table
    function appendProductToTable(stock) {
        let existing = posCart.find(item => item.stock_id === stock.id);

        if (existing) {
            if (existing.qty < stock.quantity) {
                existing.qty += 1;
                existing.subtotal = existing.qty * existing.price;
            } else {
                alert('Requested quantity exceeds available stock!');
            }
        } else {
            posCart.push({
                stock_id: stock.id,
                product_id: stock.product_id,
                name: stock.product.name,
                price: parseFloat(stock.selling_rate) || 0,
                qty: 1,
                max_qty: parseFloat(stock.quantity) || 0,
                subtotal: parseFloat(stock.selling_rate) || 0
            });
        }
        renderTable();
    }

    // Render Table DOM
    function renderTable() {
        const tbody = document.getElementById('pos-table-body');
        tbody.innerHTML = '';

        if (posCart.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-cart-row">
                    <td colspan="7" class="text-center py-5 text-muted">
                        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-secondary mb-2" />
                        <p class="mb-0 font-weight-bold">No products added yet. Select outlet and search items.</p>
                    </td>
                </tr>`;
            document.getElementById('cart-item-count').innerText = '0 Items';
            recalculateTotals();
            return;
        }

        posCart.forEach((item, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td class="text-left font-weight-bold text-dark">
                    ${item.name}
                    <input type="hidden" value="${item.price.toFixed(2)}">
                </td>
                <td><span class="badge badge-info px-2 py-1">Avail: ${item.max_qty}</span></td>
                <td>
                    <input type="text" readonly class="form-control form-control-sm text-center"
                        value="${item.price.toFixed(2)}" oninput="updateItemPrice(${index}, this.value)">
                </td>
                <td>
                    <input type="number" step="0.01" min="0.1" max="${item.max_qty}" class="form-control form-control-sm text-center item-qty-input"
                        value="${item.qty}" oninput="updateItemQty(${index}, this.value, this)">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm text-center font-weight-bold text-primary item-subtotal-input"
                        value="${item.subtotal.toFixed(2)}" oninput="updateItemSubtotal(${index}, this.value, this)">
                </td>
                <td>
                    <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeItem(${index})">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('cart-item-count').innerText = `${posCart.length} Items`;
        recalculateTotals();
    }

    function updateItemPrice(index, val) {
        let price = parseFloat(val) || 0;
        posCart[index].price = price;
        posCart[index].subtotal = price * posCart[index].qty;
        renderTable();
    }

    // Quantity change korle Subtotal update hobe
    function updateItemQty(index, val, el) {
        let qty = parseFloat(val) || 0;

        // Max stock validation check
        if (qty > posCart[index].max_qty) {
            alert('Quantity cannot exceed available stock!');
            qty = posCart[index].max_qty;
            if (el) el.value = qty;
        }

        posCart[index].qty = qty;
        posCart[index].subtotal = qty * posCart[index].price;

        // Update only the subtotal input of this row
        const row = document.getElementById('pos-table-body').children[index];
        if (row) {
            const subtotalInput = row.querySelector('.item-subtotal-input');
            if (subtotalInput) {
                subtotalInput.value = posCart[index].subtotal.toFixed(2);
            }
        }

        recalculateTotals();
    }

    // Subtotal change (vice-versa sync without full re-render)
    function updateItemSubtotal(index, val, el) {
        let subtotal = parseFloat(val) || 0;
        posCart[index].subtotal = subtotal;

        if (posCart[index].price > 0) {
            let calculatedQty = subtotal / posCart[index].price;

            if (calculatedQty > posCart[index].max_qty) {
                alert('Calculated quantity exceeds available stock!');
                calculatedQty = posCart[index].max_qty;
                posCart[index].subtotal = calculatedQty * posCart[index].price;
                if (el) el.value = posCart[index].subtotal.toFixed(2);
            }

            posCart[index].qty = parseFloat(calculatedQty.toFixed(2));

            // Update only the quantity input of this row
            const row = document.getElementById('pos-table-body').children[index];
            if (row) {
                const qtyInput = row.querySelector('.item-qty-input');
                if (qtyInput) {
                    qtyInput.value = posCart[index].qty;
                }
            }
        }

        recalculateTotals();
    }

    function removeItem(index) {
        posCart.splice(index, 1);
        renderTable();
    }

    function recalculateTotals() {
        let subtotal = posCart.reduce((acc, item) => acc + item.subtotal, 0);
        let discount = parseFloat(document.getElementById('summary-discount').value) || 0;
        let grandTotal = subtotal - discount;
        if (grandTotal < 0) grandTotal = 0;

        let paid = parseFloat(document.getElementById('summary-paid-amount').value) || 0;
        let change = paid - grandTotal;

        document.getElementById('summary-subtotal').innerText = subtotal.toFixed(2);
        document.getElementById('summary-grandtotal').innerText = grandTotal.toFixed(2);

        let changeElem = document.getElementById('summary-change');
        changeElem.innerText = change.toFixed(2);
        changeElem.className = change < 0 ? 'h5 font-weight-bold text-danger mb-0' : 'h5 font-weight-bold text-success mb-0';
    }

    function togglePaymentFields() {
        let type = document.querySelector('input[name="payment_type"]:checked').value;
        let refGroup = document.getElementById('payment_reference_group');
        let refLabel = document.getElementById('payment_ref_label');

        if (type === 'Card') {
            refGroup.style.display = 'block';
            refLabel.innerText = 'Card Approval / Transaction Code';
        } else if (type === 'MFS') {
            refGroup.style.display = 'block';
            refLabel.innerText = 'bKash / Nagad TrxID';
        } else {
            refGroup.style.display = 'none';
        }
    }

    async function storeCustomer(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch("{{ route('pos.storeCustomer') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            let option = new Option(data.customer.name, data.customer.id, true, true);
            $('#customer_id').append(option).trigger('change');
            $('#addCustomerModal').modal('hide');
            e.target.reset();
        }
    }

    async function processOrderCheckout() {
        let outletId = document.getElementById('outlet_id').value;
        let customerId = $('#customer_id').val();

        if (!outletId) {
            alert('Please select an outlet first!');
            return;
        }
        if (!customerId) {
            alert('Please select a customer!');
            return;
        }
        if (posCart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        let paymentType = document.querySelector('input[name="payment_type"]:checked').value;
        let paidAmount = parseFloat(document.getElementById('summary-paid-amount').value) || 0;
        let discount = parseFloat(document.getElementById('summary-discount').value) || 0;
        let reference = document.getElementById('payment_reference').value;

        const payload = {
            _token: '{{ csrf_token() }}',
            outlet_id: outletId,
            customer_id: customerId,
            items: posCart,
            payment_type: paymentType,
            pay_amount: paidAmount,
            discount: discount,
            payment_reference: reference
        };

        try {
            const response = await fetch("{{ route('pos.storeOrder') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if (data.success) {
                alert('Order processed successfully!');
                if (data.invoice_url) window.open(data.invoice_url, '_blank');
                posCart = [];
                renderTable();
                document.getElementById('summary-discount').value = '0.00';
                document.getElementById('summary-paid-amount').value = '';
            } else {
                alert('Error: ' + (data.message || 'Checkout failed'));
            }
        } catch (err) {
            console.error('Order error:', err);
            alert('Something went wrong during checkout.');
        }
    }
</script>
@endsection