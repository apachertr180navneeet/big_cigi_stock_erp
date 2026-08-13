@extends('admin.layouts.app')

@section('style')
<style>
    /* Excel-style items table */
    #itemsTable {
        min-width: 900px;
        border-collapse: collapse;
    }
    #itemsTable thead th {
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        padding: 10px 8px;
        background: #f0f0f0;
        border: 1px solid #999;
        color: #333;
    }
    #itemsTable tbody td {
        padding: 4px 5px;
        vertical-align: middle;
        border: 1px solid #bbb;
    }
    #itemsTable tbody tr:hover {
        background: #f8f9ff;
    }
    #itemsTable .form-control,
    #itemsTable .form-select {
        font-size: 0.9rem;
        padding: 0.5rem 0.6rem;
        min-height: 42px;
        border-radius: 4px;
    }
    #itemsTable .item-select {
        min-width: 260px !important;
    }
    #itemsTable .form-control:focus {
        box-shadow: 0 0 0 0.15rem rgba(105, 108, 255, 0.15);
    }
    #itemsTable tfoot td {
        padding: 6px 8px;
        border: 1px solid #999;
        font-weight: 700;
        background: #f9f9f9;
    }
    .summary-table {
        width: 100%;
        max-width: 420px;
        border-collapse: collapse;
    }
    .summary-table td {
        padding: 6px 12px;
        border: 1px solid #999;
        font-size: 0.9rem;
    }
    .summary-table .label-cell {
        text-align: right;
        font-weight: 600;
        background: #f9f9f9;
        white-space: nowrap;
        width: 50%;
    }
    .summary-table .value-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        width: 50%;
    }
    .summary-table .total-row td {
        background: #eef2ff;
        font-weight: 700;
        font-size: 1.05rem;
    }
    .invoice-section-card {
        border: 1px solid #d9dee3;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 20px;
    }
    .invoice-section-title {
        background: #f8f9fa;
        padding: 10px 15px;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 1px solid #d9dee3;
        border-radius: 7px 7px 0 0;
        color: #566a7f;
    }
    .amt-display {
        font-size: 0.9rem;
        font-weight: 600;
        text-align: right;
        padding: 0.45rem 0.5rem;
        background: #fafbfe;
        border-radius: 4px;
        color: #333;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        border: 1px solid #ddd;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Purchases /</span> New Purchase Bill</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="bx bx-file me-1"></i> Import Excel
            </button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm">
                @csrf

                <!-- Invoice Header Section -->
                <div class="invoice-section-card">
                    <div class="invoice-section-title">
                        <i class="bx bx-receipt me-1"></i> Tax Invoice Details
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4 border-end">
                                <label class="form-label fw-bold text-primary">Supplier (Bill From) <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-select mb-2" required>
                                    <option value="">Select Supplier / Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 border-end">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Invoice No. <span class="text-danger">*</span></label>
                                        <input type="text" name="bill_no" class="form-control" placeholder="e.g. CO8A627100003593" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Supplier Inv No.</label>
                                        <input type="text" name="supplier_invoice_no" class="form-control" placeholder="Supplier Inv No">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">e-Way Bill No.</label>
                                        <input type="text" name="eway_bill_no" class="form-control" placeholder="e-Way Bill No">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Dated <span class="text-danger">*</span></label>
                                        <input type="date" name="bill_date" class="form-control" required value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Supplier Inv Date</label>
                                        <input type="date" name="supplier_invoice_date" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Other Ref.</label>
                                        <input type="text" name="other_references" class="form-control" placeholder="Ref.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0 fw-bold"><i class="bx bx-package me-1 text-primary"></i> Description of Goods</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addRow">
                        <i class="bx bx-plus me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:5%;">Sl<br>No.</th>
                                <th style="width:38%;">Description of Goods</th>
                                <th style="width:14%;">Quantity</th>
                                <th style="width:12%;">Rate</th>
                                <th style="width:8%;">per</th>
                                <th style="width:8%;">Disc. %</th>
                                <th style="width:15%;">Amount</th>
                                <th style="width:4%;">Act</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center fw-bold sl-no">1</td>
                                <td>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[0][no_of_package]" class="package-input" value="0">
                                    <input type="hidden" name="items[0][packets]" class="packets-input" value="0">
                                    <input type="hidden" name="items[0][mrp]" class="mrp-input" value="0">
                                </td>
                                <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input text-end" required placeholder="0.00"></td>
                                <td><input type="number" step="0.0001" name="items[0][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                                <td><input type="text" name="items[0][uom]" class="form-control uom-input text-center" value="PCS"></td>
                                <td><input type="number" step="0.01" name="items[0][discount_percent]" class="form-control disc-pct-input text-end" placeholder="0"></td>
                                <td>
                                    <div class="amt-display amount-display">0.00</div>
                                    <input type="hidden" name="items[0][amount]" class="item-amount-input" value="0.00">
                                </td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end" colspan="2"><strong>Total</strong></td>
                                <td class="text-end fw-bold" id="totalQty">0.00</td>
                                <td colspan="3"></td>
                                <td class="text-end fw-bold" id="totalBasic">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary Section -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <span class="text-muted small">Amount Chargeable (in words):</span><br>
                            <strong class="text-dark fs-6" id="amountInWordsDisplay">INR Zero Only</strong>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <table class="summary-table">
                            <tr>
                                <td class="label-cell">Subtotal</td>
                                <td class="value-cell fw-semibold" id="summarySubtotal">0.00</td>
                            </tr>
                            <tr>
                                <td class="label-cell text-danger"><em>Less: Discount Allowed</em></td>
                                <td class="value-cell p-1">
                                    <input type="number" step="0.01" name="discount_allowed" id="discountAllowedInput" class="form-control text-end text-danger fw-bold" value="0.00" placeholder="0.00">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">SGST</td>
                                <td class="value-cell p-1">
                                    <input type="number" step="0.01" name="sgst_total" id="sgstTotalInput" class="form-control text-end fw-bold" value="0.00" placeholder="0.00">
                                </td>
                            </tr>
                            <tr>
                                <td class="label-cell">CGST</td>
                                <td class="value-cell p-1">
                                    <input type="number" step="0.01" name="cgst_total" id="cgstTotalInput" class="form-control text-end fw-bold" value="0.00" placeholder="0.00">
                                </td>
                            </tr>
                            <tr class="total-row">
                                <td class="label-cell">₹ Grand Total</td>
                                <td class="value-cell">
                                    <span id="summaryGrandTotal" class="text-primary fw-bold fs-5">0.00</span>
                                    <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Save Purchase Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Excel Import Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-file me-1 text-success"></i> Import Purchase Invoice from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="excelImportForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">Upload Tally / ITC Excel Tax Invoice (.xlsx, .xls, .csv). System extracts Supplier, Invoice No., Date, and Items automatically.</div>
                    <div id="importAlert" class="alert d-none py-2 small"></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Excel Invoice File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.purchases.template') }}" class="btn btn-sm btn-outline-secondary me-auto">Download Template</a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btnUploadExcel"><i class="bx bx-upload me-1"></i> Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const itemData = {
        @foreach($items as $item)
            "{{ $item->id }}": {
                hsn: "{{ $item->hsn ?? '' }}",
                brand_code: "{{ $item->brand_code ?? '' }}",
                pack_size: {{ $item->pack_size ?? 1 }},
                sale_price: {{ $item->sale_price ?? 0 }}
            },
        @endforeach
    };

    let rowIdx = 1;

    function calculateRow(row) {
        let qty = parseFloat(row.find('.qty-input').val()) || 0;
        let rate = parseFloat(row.find('.rate-input').val()) || 0;
        let discPct = parseFloat(row.find('.disc-pct-input').val()) || 0;

        let amount = qty * rate;
        if (discPct > 0) {
            amount = amount - (amount * (discPct / 100));
        }

        row.find('.amount-display').text(amount.toFixed(2));
        row.find('.item-amount-input').val(amount.toFixed(2));

        let packetsInput = row.find('.packets-input');
        let mrpInput = row.find('.mrp-input');
        if (!packetsInput.val() || parseFloat(packetsInput.val()) === 0) packetsInput.val(qty);
        if (!mrpInput.val() || parseFloat(mrpInput.val()) === 0) mrpInput.val(rate);

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0, subtotal = 0;

        $('#itemsTable tbody tr').each(function() {
            let row = $(this);
            let q = parseFloat(row.find('.qty-input').val()) || 0;
            let amt = parseFloat(row.find('.item-amount-input').val()) || (q * (parseFloat(row.find('.rate-input').val()) || 0));
            totalQty += q;
            subtotal += amt;
        });

        let discountAllowed = parseFloat($('#discountAllowedInput').val()) || 0;
        let sgstTotal = parseFloat($('#sgstTotalInput').val()) || 0;
        let cgstTotal = parseFloat($('#cgstTotalInput').val()) || 0;

        let netTaxable = Math.max(0, subtotal - discountAllowed);
        let grandTotal = netTaxable + sgstTotal + cgstTotal;

        $('#totalQty').text(totalQty.toFixed(2));
        $('#totalBasic').text(subtotal.toFixed(2));
        $('#summarySubtotal').text(subtotal.toFixed(2));
        $('#summaryGrandTotal').text(grandTotal.toFixed(2));
        $('#totalAmountInput').val(grandTotal.toFixed(2));

        updateAmountInWords(grandTotal);
        updateSlNumbers();
    }

    function updateSlNumbers() {
        $('#itemsTable tbody tr').each(function(i) {
            $(this).find('.sl-no').text(i + 1);
        });
    }

    function updateAmountInWords(amount) {
        if (amount <= 0) { $('#amountInWordsDisplay').text('INR Zero Only'); return; }
        $('#amountInWordsDisplay').text('INR ' + numberToWords(amount) + ' Only');
    }

    function numberToWords(num) {
        let a = ['','One ','Two ','Three ','Four ','Five ','Six ','Seven ','Eight ','Nine ','Ten ','Eleven ','Twelve ','Thirteen ','Fourteen ','Fifteen ','Sixteen ','Seventeen ','Eighteen ','Nineteen '];
        let b = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        let n = ('000000000' + Math.floor(num)).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        if (!n) return '';
        let str = '';
        str += (n[1]!=0)?(a[Number(n[1])]||b[n[1][0]]+' '+a[n[1][1]])+'Crore ':'';
        str += (n[2]!=0)?(a[Number(n[2])]||b[n[2][0]]+' '+a[n[2][1]])+'Lakh ':'';
        str += (n[3]!=0)?(a[Number(n[3])]||b[n[3][0]]+' '+a[n[3][1]])+'Thousand ':'';
        str += (n[4]!=0)?(a[Number(n[4])]||b[n[4][0]]+' '+a[n[4][1]])+'Hundred ':'';
        str += (n[5]!=0)?((str!='')?'and ':'')+(a[Number(n[5])]||b[n[5][0]]+' '+a[n[5][1]]):'';
        let rupees = str.trim();
        let paise = Math.round((num - Math.floor(num)) * 100);
        let paiseStr = '';
        if (paise > 0) {
            let p1 = Math.floor(paise / 10);
            let p2 = paise % 10;
            paiseStr = ' and ' + (paise < 20 ? a[paise] : b[p1] + ' ' + a[p2]) + 'Paise';
        }
        return rupees + paiseStr;
    }

    $(document).ready(function() {
        $('#addRow').click(function() {
            let options = '<option value="">Select Item</option>';
            @foreach($items as $item)
                options += '<option value="{{ $item->id }}">{{ addslashes($item->name) }}</option>';
            @endforeach
            let newRow = `<tr>
                <td class="text-center fw-bold sl-no">${rowIdx+1}</td>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>${options}</select>
                    <input type="hidden" name="items[${rowIdx}][no_of_package]" class="package-input" value="0">
                    <input type="hidden" name="items[${rowIdx}][packets]" class="packets-input" value="0">
                    <input type="hidden" name="items[${rowIdx}][mrp]" class="mrp-input" value="0">
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" required placeholder="0.00"></td>
                <td><input type="number" step="0.0001" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                <td><input type="text" name="items[${rowIdx}][uom]" class="form-control uom-input text-center" value="PCS"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_percent]" class="form-control disc-pct-input text-end" placeholder="0"></td>
                <td>
                    <div class="amt-display amount-display">0.00</div>
                    <input type="hidden" name="items[${rowIdx}][amount]" class="item-amount-input" value="0.00">
                </td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            </tr>`;
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
            updateSlNumbers();
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#itemsTable tbody tr').length > 1) {
                let row = $(this).closest('tr');
                Swal.fire({
                    title: 'Remove Item?',
                    text: 'Are you sure you want to remove this item row?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff3e1d',
                    cancelButtonColor: '#8592a3',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.remove();
                        calculateGrandTotal();
                        Toast.fire({ icon: 'success', title: 'Item row removed' });
                    }
                });
            }
        });

        $(document).on('input', '.qty-input, .rate-input, .disc-pct-input', function() {
            calculateRow($(this).closest('tr'));
        });

        $(document).on('input', '#discountAllowedInput, #sgstTotalInput, #cgstTotalInput', function() {
            calculateGrandTotal();
        });

        // Excel Import Handler
        $('#excelImportForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let btn = $('#btnUploadExcel');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Parsing...');

            $.ajax({
                url: "{{ route('admin.purchases.parse_excel') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i> Upload & Import');
                    if (res.success) {
                        if (res.vendor_id) {
                            if ($('select[name="vendor_id"] option[value="'+res.vendor_id+'"]').length === 0) {
                                $('select[name="vendor_id"]').append(new Option(res.vendor_name || 'Vendor', res.vendor_id, true, true));
                            } else {
                                $('select[name="vendor_id"]').val(res.vendor_id);
                            }
                        }
                        if (res.bill_no) $('input[name="bill_no"]').val(res.bill_no);
                        if (res.bill_date) $('input[name="bill_date"]').val(res.bill_date);

                        if (res.discount_allowed !== undefined) {
                            $('#discountAllowedInput').val(parseFloat(res.discount_allowed).toFixed(2));
                        }
                        if (res.sgst_total !== undefined) {
                            $('#sgstTotalInput').val(parseFloat(res.sgst_total).toFixed(2));
                        }
                        if (res.cgst_total !== undefined) {
                            $('#cgstTotalInput').val(parseFloat(res.cgst_total).toFixed(2));
                        }

                        if (res.items && res.items.length > 0) {
                            let isFirstEmpty = $('#itemsTable tbody tr').length === 1 && !$('#itemsTable tbody tr:first .item-select').val();
                            if (isFirstEmpty) $('#itemsTable tbody').empty();

                            res.items.forEach(function(item) { appendImportedRow(item); });
                            calculateGrandTotal();
                            $('#importExcelModal').modal('hide');
                            $('#excelImportForm')[0].reset();
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Successful!',
                                text: res.message,
                                confirmButtonColor: '#696cff'
                            });
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Import Failed', text: res.message || 'Error parsing Excel.', confirmButtonColor: '#696cff' });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i> Upload & Import');
                    Swal.fire({ icon: 'error', title: 'Import Error', text: xhr.responseJSON?.message || 'Import failed.', confirmButtonColor: '#696cff' });
                }
            });
        });

        function appendImportedRow(item) {
            let options = '<option value="">Select Item</option>';
            @foreach($items as $itm)
                let sel{{ $itm->id }} = ({{ $itm->id }} == item.item_id) ? 'selected' : '';
                options += `<option value="{{ $itm->id }}" ${sel{{ $itm->id }}}>{{ addslashes($itm->name) }}</option>`;
            @endforeach
            if (options.indexOf(`value="${item.item_id}"`) === -1) {
                options += `<option value="${item.item_id}" selected>${item.item_name}</option>`;
                itemData[item.item_id] = { hsn: item.hsn||'', brand_code: item.brand_code||'', pack_size: 1, sale_price: item.rate||0 };
            }

            let itemAmt = item.amount ? parseFloat(item.amount).toFixed(2) : (parseFloat(item.quantity||0) * parseFloat(item.rate||0)).toFixed(2);

            let newRow = $(`<tr>
                <td class="text-center fw-bold sl-no">${rowIdx+1}</td>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>${options}</select>
                    <input type="hidden" name="items[${rowIdx}][no_of_package]" class="package-input" value="${item.no_of_package||0}">
                    <input type="hidden" name="items[${rowIdx}][packets]" class="packets-input" value="${item.packets||item.quantity||0}">
                    <input type="hidden" name="items[${rowIdx}][mrp]" class="mrp-input" value="${item.mrp||item.rate||0}">
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" value="${item.quantity||0}" required></td>
                <td><input type="number" step="0.0001" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" value="${item.rate||0}" required></td>
                <td><input type="text" name="items[${rowIdx}][uom]" class="form-control uom-input text-center" value="${item.uom||'PCS'}"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_percent]" class="form-control disc-pct-input text-end" value="${item.discount_percent||''}"></td>
                <td>
                    <div class="amt-display amount-display">${itemAmt}</div>
                    <input type="hidden" name="items[${rowIdx}][amount]" class="item-amount-input" value="${itemAmt}">
                </td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            </tr>`);
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
            updateSlNumbers();
        }
    });
</script>
@endsection
