@extends('dashboard.body.main')

@section('specificpagestyles')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title">Add Outlet</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('outlets.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <input type="hidden" name="row_id" value="{{ $outlet->id ?? '' }}">

                            <!-- begin: Input Data -->
                            <div class="row align-items-center">
                                <div class="form-group col-md-6">
                                    <label for="name">Outlet Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                        value="{{ old('name') ?? $outlet->name ?? '' }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="parent_id">Parent Outlet</label>
                                    <select class="form-control" name="parent_id">
                                        <option selected="" disabled>-- Select outlet --</option>
                                        @foreach ($outlets as $item)                        

                                            @if(isset($outlet) && $outlet->id == $item->id) 
                                                @continue 
                                            @endif

                                            <option value="{{ $item->id }}" 
                                                {{ old('parent_id', isset($outlet) ? $outlet->parent_id : '') == $item->id ? 'selected' : '' }}>
                                                {{ $item->code }} - {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" name="address" id="address">{{ $outlet->address ?? '' }}</textarea>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <!-- end: Input Data -->

                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <x-heroicon-o-check-circle class="w-5 h-5 mr-1 inline" /> Save
                                </button>
                                <a class="btn btn-danger" href="{{ route('outlets.index') }}">
                                    <x-heroicon-o-x-mark class="w-5 h-5 mr-1 inline" /> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- Page end  -->
    </div>

    <script>
        $('#buying_date').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
            // https://gijgo.com/datetimepicker/configuration/format
        });
        $('#expire_date').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd'
            // https://gijgo.com/datetimepicker/configuration/format
        });

        // Barcode Scanner Handling
        (function() {
            const barcodeScanner = document.getElementById('barcode_scanner');
            const codeField = document.getElementById('code');
            
            if (barcodeScanner && codeField) {
                let scannerTimeout;
                
                // Auto-focus scanner field on mobile devices
                function isMobileDevice() {
                    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                           (window.innerWidth <= 768);
                }
                
                if (isMobileDevice()) {
                    // Auto-focus scanner field on mobile after a short delay
                    setTimeout(function() {
                        barcodeScanner.focus();
                    }, 300);
                }
                
                // Handle scanner input (scanners typically send Enter after barcode)
                barcodeScanner.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        
                        const scannedValue = barcodeScanner.value.trim();
                        if (scannedValue) {
                            // Populate code field with scanned value
                            codeField.value = scannedValue;
                            
                            // Visual feedback
                            codeField.classList.add('border-success');
                            setTimeout(function() {
                                codeField.classList.remove('border-success');
                            }, 1000);
                            
                            // Clear scanner field
                            barcodeScanner.value = '';
                            
                            // Re-focus scanner for next scan
                            if (isMobileDevice()) {
                                setTimeout(function() {
                                    barcodeScanner.focus();
                                }, 100);
                            }
                        }
                    }
                });
                
                // Handle paste events (some scanners use paste)
                barcodeScanner.addEventListener('paste', function(e) {
                    setTimeout(function() {
                        const pastedValue = barcodeScanner.value.trim();
                        if (pastedValue) {
                            codeField.value = pastedValue;
                            codeField.classList.add('border-success');
                            setTimeout(function() {
                                codeField.classList.remove('border-success');
                            }, 1000);
                            barcodeScanner.value = '';
                        }
                    }, 10);
                });
            }
        })();
    </script>

    @include('components.preview-img-form')
@endsection
