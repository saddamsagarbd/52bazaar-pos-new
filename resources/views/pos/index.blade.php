@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
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

    <!-- MAIN POS CONTAINER TABLE-BASED LAYOUT -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between py-3">
                    <h4 class="mb-0 text-white font-weight-bold">
                        <x-heroicon-o-shopping-cart class="w-6 h-6 mr-2 inline" /> POS Billing Terminal
                    </h4>
                    <div>
                        <span class="badge badge-light text-primary font-weight-bold px-3 py-2" id="cart-item-count">0 Items</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- TOP BAR: Search & Customer Selection -->
                    <div class="row mb-4">
                        <!-- Search & Barcode Scan Input -->
                        <div class="col-md-7">
                            <label class="font-weight-bold text-dark mb-2">Scan Barcode / Search Product (Name/SKU/Code)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">
                                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                                    </span>
                                </div>
                                <input type="text" id="pos_product_search" class="form-control form-control-lg border-primary" 
                                    placeholder="Scan barcode or type product name/SKU..." autofocus autocomplete="off">
                            </div>
                            <div id="search_results_dropdown" class="dropdown-menu w-100 shadow-lg" style="max-height: 300px; overflow-y: auto;"></div>
                        </div>

                        <!-- Customer Selection -->
                        <div class="col-md-5">
                            <label class="font-weight-bold text-dark mb-2">Customer</label>
                            <div class="input-group">
                                <select class="form-control select2" id="customer_id" name="customer_id" style="width: 80%;">
                                    <option value="" selected disabled>-- Search / Select Customer --</option>
                                </select>
                                <div class="input-group-append" style="width: 20%;">
                                    <button type="button" class="btn btn-outline-primary btn-block" title="Add Customer" data-toggle="modal" data-target="#addCustomerModal">
                                        <x-heroicon-o-plus class="w-5 h-5 mx-auto" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POS ITEMS TABLE -->
                    <div class="table-responsive mb-4" style="min-height: 250px; max-height: 450px; overflow-y: auto;">
                        <table class="table table-bordered table-hover align-middle text-center mb-0" id="pos-table">
                            <thead class="bg-light text-dark font-weight-bold">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;" class="text-left">Product Name</th>
                                    <th style="width: 15%;">Unit Price</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 20%;">Sub Total</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="pos-table-body">
                                <tr id="empty-cart-row">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-secondary mb-2" />
                                        <p class="mb-0 font-weight-bold">No products appended yet. Scan or search to add items.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTTOM SUMMARY & PAYMENT PANEL -->
                    <div class="row pt-3 border-top bg-light rounded-lg p-3">
                        <!-- Billing Summary -->
                        <div class="col-md-6 border-right">
                            <h5 class="font-weight-bold text-dark mb-3">Order Calculations</h5>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-muted">Sub Total:</span>
                                <span class="h5 font-weight-bold text-dark mb-0" id="summary-subtotal">0.00</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-muted">Discount (৳):</span>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-right font-weight-bold" 
                                    id="summary-discount" value="0.00" style="width: 150px;" oninput="recalculateTotals()">
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="h5 font-weight-bold text-dark">Grand Total:</span>
                                <span class="h4 font-weight-bold text-primary mb-0" id="summary-grandtotal">0.00</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="font-weight-bold text-muted">Return/Change Amount:</span>
                                <span class="h5 font-weight-bold text-success mb-0" id="summary-change">0.00</span>
                            </div>
                        </div>

                        <!-- Payment Methods & Checkout -->
                        <div class="col-md-6 pl-md-4">
                            <h5 class="font-weight-bold text-dark mb-3">Payment Details</h5>

                            <!-- Payment Method Options -->
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Select Payment Method</label>
                                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-primary active">
                                        <input type="radio" name="payment_type" id="pay_cash" value="Cash" checked onchange="togglePaymentFields()"> Cash
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="payment_type" id="pay_card" value="Card" onchange="togglePaymentFields()"> Card
                                    </label>
                                    <label class="btn btn-outline-primary">
                                        <input type="radio" name="payment_type" id="pay_mfs" value="MFS" onchange="togglePaymentFields()"> Mobile Banking (bKash/Nagad)
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Details Extra Field (Card/MFS Info) -->
                            <div class="form-group mb-3" id="payment_reference_group" style="display: none;">
                                <label class="font-weight-bold text-dark" id="payment_ref_label">Transaction / Reference No.</label>
                                <input type="text" id="payment_reference" class="form-control" placeholder="Enter Transaction ID or Card Approval Code">
                            </div>

                            <!-- Amount Paying -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark">Paid Amount (৳)</label>
                                <input type="number" step="0.01" min="0" id="summary-paid-amount" class="form-control form-control-lg font-weight-bold text-success" 
                                    placeholder="0.00" oninput="recalculateTotals()">
                            </div>

                            <!-- Submit Checkout Button -->
                            <button type="button" class="btn btn-primary btn-lg btn-block shadow font-weight-bold py-3" onclick="processOrderCheckout()">
                                <x-heroicon-o-check-circle class="w-6 h-6 inline mr-1" /> Complete & Print Invoice
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
        <div class="modal-content border-0 shadow-lg">
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
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
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
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let posCart = [];

    // Select2 Initialization
    window.addEventListener('load', function () {
        $('.select2').select2({
            placeholder: "-- Search / Select Customer --",
            allowClear: true,
            ajax: {
                url: "{{ route('pos.customers.search') }}",
                dataType: 'json',
                delay: 250,
                data: params => ({ term: params.term }),
                processResults: data => ({ results: data.results }),
                cache: true
            }
        });
    });

    // 1. Live Search Input & Barcode Scanning Logic
    const searchInput = document.getElementById('pos_product_search');
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 2) {
            document.getElementById('search_results_dropdown').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('pos.product.search') }}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                let dropdown = document.getElementById('search_results_dropdown');
                dropdown.innerHTML = '';
                
                if (data.products && data.products.length > 0) {
                    // Exact Barcode Auto Match
                    if (data.products.length === 1 && data.products[0].code === query) {
                        appendProductToTable(data.products[0]);
                        searchInput.value = '';
                        dropdown.style.display = 'none';
                        return;
                    }

                    data.products.forEach(prod => {
                        console.log(prod);
                        let item = document.createElement('a');
                        item.className = 'dropdown-item d-flex justify-content-between align-items-center py-2 border-bottom';
                        item.href = 'javascript:void(0)';
                        item.innerHTML = `
                            <div>
                                <strong>${prod.name}</strong> <small class="text-muted">(${prod.code || 'N/A'})</small>
                            </div>
                            <span class="badge badge-primary">৳${prod.selling_price} | Stock: ${prod.stock}</span>
                        `;
                        item.onclick = function() {
                            appendProductToTable(prod);
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

    // 2. Append Product to Cart Table
    function appendProductToTable(product) {
        let existing = posCart.find(item => item.id === product.id);

        if (existing) {
            existing.qty += 1;
            existing.subtotal = existing.qty * existing.price;
        } else {
            posCart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.selling_price) || 0,
                qty: 1,
                subtotal: parseFloat(product.selling_price) || 0
            });
        }
        renderTable();
    }

    // 3. Render Table DOM
    function renderTable() {
        const tbody = document.getElementById('pos-table-body');
        tbody.innerHTML = '';

        if (posCart.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-cart-row">
                    <td colspan="6" class="text-center py-5 text-muted">
                        <x-heroicon-o-shopping-bag class="w-12 h-12 mx-auto text-secondary mb-2" />
                        <p class="mb-0 font-weight-bold">No products appended yet. Scan or search to add items.</p>
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
                <td class="text-left font-weight-bold text-dark">${item.name}</td>
                <td>
                    <input type="number" readonly class="form-control form-control-sm text-center" 
                        value="${item.price.toFixed(2)}" oninput="updateItemPrice(${index}, this.value)">
                </td>
                <td>
                    <input type="number" min="1" class="form-control form-control-sm text-center" 
                        value="${item.qty}" oninput="updateItemQty(${index}, this.value)">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-center font-weight-bold text-primary" 
                        value="${item.subtotal.toFixed(2)}" onchange="updateItemSubtotal(${index}, this.value)">
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

    // Two-Way Interactive Calculations
    function updateItemPrice(index, val) {
        let price = parseFloat(val) || 0;
        posCart[index].price = price;
        posCart[index].subtotal = price * posCart[index].qty;
        renderTable();
    }

    function updateItemQty(index, val) {
        let qty = parseFloat(val) || 1;
        posCart[index].qty = qty;
        posCart[index].subtotal = posCart[index].price * qty;
        renderTable();
    }

    function updateItemSubtotal(index, val) {
        let subtotal = parseFloat(val) || 0;
        posCart[index].subtotal = subtotal;
        if (posCart[index].price > 0) {
            posCart[index].qty = (subtotal / posCart[index].price).toFixed(2);
        }
        renderTable();
    }

    function removeItem(index) {
        posCart.splice(index, 1);
        renderTable();
    }

    // Bottom Summary & Payment Calculations
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
        if (change < 0) {
            changeElem.className = 'h5 font-weight-bold text-danger mb-0';
        } else {
            changeElem.className = 'h5 font-weight-bold text-success mb-0';
        }
    }

    // Toggle Payment Input Fields
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

    // Add Customer AJAX
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

    // Process Order AJAX
    async function processOrderCheckout() {
        let customerId = $('#customer_id').val();
        if (!customerId) {
            alert('Please select a customer first!');
            return;
        }

        if (posCart.length === 0) {
            alert('Your cart is empty! Please append products first.');
            return;
        }

        let paymentType = document.querySelector('input[name="payment_type"]:checked').value;
        let paidAmount = parseFloat(document.getElementById('summary-paid-amount').value) || 0;
        let discount = parseFloat(document.getElementById('summary-discount').value) || 0;
        let reference = document.getElementById('payment_reference').value;

        const payload = {
            _token: '{{ csrf_token() }}',
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
                alert('Error: ' + (data.message || 'Payment failed'));
            }
        } catch (err) {
            console.error('Order error:', err);
            alert('Something went wrong during checkout.');
        }
    }
</script>
@endsection