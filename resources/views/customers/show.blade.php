@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Card Header & Profile Info -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Customer Details & Ledger</h4>
                        </div>
                        <div>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary font-size-14">Edit Profile</a>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary font-size-14">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Section: Left Profile (Contact) -->
                            <div class="col-lg-4 col-md-12 mb-4 mb-lg-0 pr-lg-4 border-lg-right">
                                <div class="d-flex align-items-center mb-4 justify-content-center justify-content-lg-start">
                                    <div class="ml-3">
                                        <h4 class="mb-1">{{ $customer->name }}</h4>
                                        <p class="mb-2 text-muted">Customer ID: #{{ $customer->id }}</p>
                                    </div>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="d-flex align-items-center">
                                            <x-heroicon-o-envelope class="w-6 h-6 mr-3 text-primary" />
                                            <span class="mb-0">{{ $customer->email ?? 'N/A' }}</span>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="d-flex align-items-center">
                                            <x-heroicon-o-phone class="w-6 h-6 mr-3 text-primary" />
                                            <span class="mb-0">{{ $customer->phone ?? 'N/A' }}</span>
                                        </div>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div class="d-flex align-items-center">
                                            <x-heroicon-o-map-pin class="w-6 h-6 mr-3 text-primary" />
                                            <span class="mb-0">{{ $customer->city ?? 'Unknown' }}</span>
                                        </div>
                                    </li>
                                </ul>

                                <!-- Quick Financial Summary Box (Accounting Base) -->
                                <div class="card bg-light border-0 mt-4 p-3">
                                    <h6 class="font-weight-bold text-dark mb-2">Account Summary</h6>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Total Orders:</span>
                                        <span class="font-weight-bold">{{ $customer->orders->count() }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Current Total Due:</span>
                                        <span class="font-weight-bold text-danger">৳{{ number_format($customer->orders->sum('due_amount'), 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Right Detailed Info -->
                            <div class="col-lg-8 col-md-12 pl-lg-4">
                                <h5 class="mb-3">Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted">Full Name</h6>
                                        <p class="font-weight-bold">{{ $customer->name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted">Email</h6>
                                        <p class="font-weight-bold">{{ $customer->email ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted">Phone</h6>
                                        <p class="font-weight-bold">{{ $customer->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted">City</h6>
                                        <p class="font-weight-bold">{{ $customer->city ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <h6 class="text-muted">Address</h6>
                                        <p class="font-weight-bold">{{ $customer->address ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <h6 class="text-muted">Joined At</h6>
                                        <p class="font-weight-bold">{{ $customer->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Customer Ledger / Transaction History (Accounting Module Foundation) -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title text-white mb-0">Customer Ledger & Transaction History</h4>
                        
                        @if($customer->orders->sum('due_amount') > 0)
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#payDueModal">
                                <x-heroicon-o-banknotes class="w-4 h-4 mr-1" /> Pay Due
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 table-bordered table-striped text-center">
                                <thead class="bg-white text-uppercase">
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice No</th>
                                        <th>Total Bill (৳)</th>
                                        <th>Paid (৳)</th>
                                        <th>Due (৳)</th>
                                        <th>Payment Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->orders as $order)
                                        <tr>
                                            <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                            <td><span class="font-weight-bold text-primary">{{ $order->invoice_no }}</span></td>
                                            <td>৳{{ number_format($order->total, 2) }}</td>
                                            <td><span class="text-success font-weight-bold">৳{{ number_format($order->pay_amount, 2) }}</span></td>
                                            <td><span class="text-danger font-weight-bold">৳{{ number_format($order->due_amount, 2) }}</span></td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $order->payment_type }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $order->order_status == 'complete' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($order->order_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('order.orderDetails', $order->id) }}" class="btn btn-sm btn-info" title="View Invoice">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>
                                                @if($order->due_amount > 0)
                                                    <button type="button" class="btn btn-primary bg-primary mr-2 border-0" data-toggle="modal"
                                                        data-target="#pay-due-modal" id="{{ $order->id }}" onclick="payDue(this.id)">
                                                        <x-heroicon-o-banknotes class="w-5 h-5 mr-0" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No transaction history found for this customer.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
        <!-- Modal: Pay Due Amount -->
    <div class="modal fade" id="pay-due-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Pay Due Amount</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('order.updateDue') }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="order_id">
                        <input type="hidden" name="customer_id" id="customer_id">
                        <div class="form-group">
                            <label for="due_amount">Pay Amount</label>
                            <input type="number" class="form-control" name="due_amount" id="due_amount" required step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Pay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pay Due Modal -->
    <div class="modal fade" id="payDueModal" tabindex="-1" role="dialog" aria-labelledby="payDueModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="payDueModalLabel">Collect Due Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="payDueForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Total Current Due:</label>
                            <input type="text" class="form-control font-weight-bold text-danger" value="৳{{ number_format($customer->orders->sum('due_amount'), 2) }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="amount">Payment Amount (৳): <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.1" max="{{ $customer->orders->sum('due_amount') }}" class="form-control" name="amount" id="amount" placeholder="Enter amount to pay" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function payDue(id) {
            $.ajax({
                type: 'GET',
                url: '/order/due/' + id,
                dataType: 'json',
                success: function (data) {
                    $('#due_amount').val(data.due_amount);
                    $('#order_id').val(data.id);
                    $('#customer_id').val(data.customer_id);
                }
            });
        }
    </script>
@endsection

@section('specificpagescripts')
<script>
    document.getElementById('payDueForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Processing...';

        fetch("{{ route('customer.payDue', $customer->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Alert message
                location.reload(); // Page reload to update data
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong!');
            submitBtn.disabled,submitBtn.disabled = false;
            submitBtn.innerText = 'Submit Payment';
        });
    });
</script>
@endsection