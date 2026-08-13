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
        font-size: 0.85rem;
        padding: 0.35rem 0.45rem;
        min-height: 34px;
        border-radius: 3px;
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
        width: auto;
        min-width: 380px;
        border-collapse: collapse;
    }
    .summary-table td {
        padding: 6px 14px;
        border: 1px solid #999;
        font-size: 0.9rem;
    }
    .summary-table .label-cell {
        text-align: right;
        font-weight: 600;
        background: #f9f9f9;
        white-space: nowrap;
    }
    .summary-table .value-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        min-width: 140px;
    }
    .summary-table .total-row td {
        background: #eef2ff;
        font-weight: 700;
        font-size: 1rem;
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
        font-size: 0.85rem;
        font-weight: 600;
        text-align: right;
        padding: 0.35rem 0.5rem;
        background: #fafbfe;
        border-radius: 3px;
        color: #333;
        min-height: 34px;
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
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Purchases /</span> Edit Purchase #{{ $purchase->bill_no }}</h4>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
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

            <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" id="purchaseForm">
                @csrf
                @method('PUT')

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
                                        <option value="{{ $vendor->id }}" {{ $purchase->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 border-end">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Invoice No. <span class="text-danger">*</span></label>
                                        <input type="text" name="bill_no" class="form-control" value="{{ $purchase->bill_no }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Supplier Inv No.</label>
                                        <input type="text" name="supplier_invoice_no" class="form-control" value="{{ $purchase->supplier_invoice_no ?? '' }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">e-Way Bill No.</label>
                                        <input type="text" name="eway_bill_no" class="form-control" value="{{ $purchase->eway_bill_no ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Dated <span class="text-danger">*</span></label>
                                        <input type="date" name="bill_date" class="form-control" required value="{{ $purchase->bill_date }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Supplier Inv Date</label>
                                        <input type="date" name="supplier_invoice_date" class="form-control" value="{{ $purchase->supplier_invoice_date ?? '' }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Other Ref.</label>
                                        <input type="text" name="other_references" class="form-control" value="{{ $purchase->other_references ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table (Excel style: Sl No. | Description of Goods | Quantity | Rate | per | Disc. % | Amount) -->
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
                                <th style="width:4%;">Sl<br>No.</th>
                                <th style="width:32%;">Description of Goods</th>
                                <th style="width:13%;">Quantity</th>
                                <th style="width:10%;">Rate</th>
                                <th style="width:7%;">per</th>
                                <th style="width:8%;">Disc. %</th>
                                <th style="width:14%;">Amount</th>
                                <th style="width:4%;">Act</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $idx => $pItem)
                            <tr>
                                <td class="text-center fw-bold sl-no">{{ $idx + 1 }}</td>
                                <td>
                                    <select name="items[{{ $idx }}][item_id]" class="form-select item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" {{ $pItem->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="items[{{ $idx }}][no_of_package]" class="package-input" value="{{ $pItem->no_of_package ?? 0 }}">
                                    <input type="hidden" name="items[{{ $idx }}][packets]" class="packets-input" value="{{ $pItem->packets ?? $pItem->quantity }}">
                                    <input type="hidden" name="items[{{ $idx }}][mrp]" class="mrp-input" value="{{ $pItem->mrp ?? $pItem->rate }}">
                                    <input type="hidden" name="items[{{ $idx }}][cgst_rate]" class="cgst-rate-input" value="{{ $pItem->cgst_rate ?? 20.00 }}">
                                    <input type="hidden" name="items[{{ $idx }}][sgst_rate]" class="sgst-rate-input" value="{{ $pItem->sgst_rate ?? 20.00 }}">
                                </td>
                                <td><input type="number" step="0.01" name="items[{{ $idx }}][quantity]" class="form-control qty-input text-end" value="{{ $pItem->quantity }}" required></td>
                                <td><input type="number" step="0.01" name="items[{{ $idx }}][rate]" class="form-control rate-input text-end" value="{{ $pItem->rate }}" required></td>
                                <td><input type="text" name="items[{{ $idx }}][uom]" class="form-control uom-input text-center" value="{{ $pItem->uom ?? 'PCS' }}"></td>
                                <td><input type="number" step="0.01" name="items[{{ $idx }}][discount_amount]" class="form-control discount-input text-end" value="{{ $pItem->discount_amount ?? 0 }}"></td>
                                <td><div class="amt-display amount-display">{{ number_format(($pItem->quantity * $pItem->rate) - ($pItem->discount_amount ?? 0), 2) }}</div></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end" colspan="2"><strong>Total</strong></td>
                                <td class="text-end" id="totalQty">0.00</td>
                                <td colspan="3"></td>
                                <td class="text-end" id="totalBasic">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary section (same as Excel footer) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <span class="text-muted small">Amount Chargeable (in words):</span><br>
                            <strong class="text-dark" id="amountInWordsDisplay">INR Zero Only</strong>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <table class="summary-table">
                            <tr>
                                <td class="label-cell">Subtotal</td>
                                <td class="value-cell" id="summarySubtotal">0.00</td>
                            </tr>
                            <tr>
                                <td class="label-cell text-danger"><em>Less: Discount Allowed</em></td>
                                <td class="value-cell text-danger" id="summaryDiscount">0.00</td>
                            </tr>
                            <tr>
                                <td class="label-cell">SGST</td>
                                <td class="value-cell" id="summarySGST">0.00</td>
                            </tr>
                            <tr>
                                <td class="label-cell">CGST</td>
                                <td class="value-cell" id="summaryCGST">0.00</td>
                            </tr>
                            <tr class="total-row">
                                <td class="label-cell">₹ Grand Total</td>
                                <td class="value-cell" id="summaryGrandTotal">0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Update Purchase Bill</button>
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

    let rowIdx = {{ count($purchase->items) }};

    function calculateRow(row) {
        let qty = parseFloat(row.find('.qty-input').val()) || 0;
        let rate = parseFloat(row.find('.rate-input').val()) || 0;
        let discount = parseFloat(row.find('.discount-input').val()) || 0;
        let cgstRate = parseFloat(row.find('.cgst-rate-input').val()) || 0;
        let sgstRate = parseFloat(row.find('.sgst-rate-input').val()) || 0;

        // Auto-fill hidden fields
        let packetsInput = row.find('.packets-input');
        let mrpInput = row.find('.mrp-input');
        if (!packetsInput.val() || parseFloat(packetsInput.val()) === 0) packetsInput.val(qty);
        if (!mrpInput.val() || parseFloat(mrpInput.val()) === 0) mrpInput.val(rate);

        let basicValue = qty * rate;
        let amount = basicValue - discount;
        row.find('.amount-display').text(amount.toFixed(2));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0, subtotal = 0, totalDiscount = 0;

        $('#itemsTable tbody tr').each(function() {
            let row = $(this);
            totalQty += parseFloat(row.find('.qty-input').val()) || 0;
            let q = parseFloat(row.find('.qty-input').val()) || 0;
            let r = parseFloat(row.find('.rate-input').val()) || 0;
            let d = parseFloat(row.find('.discount-input').val()) || 0;
            subtotal += (q * r);
            totalDiscount += d;
        });

        let netAmount = subtotal - totalDiscount;
        let cgstRate = parseFloat($('#itemsTable tbody tr:first .cgst-rate-input').val()) || 20;
        let sgstRate = parseFloat($('#itemsTable tbody tr:first .sgst-rate-input').val()) || 20;
        let sgstAmt = netAmount * (sgstRate / 100);
        let cgstAmt = netAmount * (cgstRate / 100);
        let grandTotal = netAmount + sgstAmt + cgstAmt;

        $('#totalQty').text(totalQty.toFixed(2));
        $('#totalBasic').text(subtotal.toFixed(2));
        $('#summarySubtotal').text(subtotal.toFixed(2));
        $('#summaryDiscount').text(totalDiscount > 0 ? '-' + totalDiscount.toFixed(2) : '0.00');
        $('#summarySGST').text(sgstAmt.toFixed(2));
        $('#summaryCGST').text(cgstAmt.toFixed(2));
        $('#summaryGrandTotal').text(grandTotal.toFixed(2));
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
        return str.trim();
    }

    $(document).ready(function() {
        // Calculate on load for existing items
        $('#itemsTable tbody tr').each(function() {
            calculateRow($(this));
        });

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
                    <input type="hidden" name="items[${rowIdx}][cgst_rate]" class="cgst-rate-input" value="20.00">
                    <input type="hidden" name="items[${rowIdx}][sgst_rate]" class="sgst-rate-input" value="20.00">
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" required placeholder="0.00"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                <td><input type="text" name="items[${rowIdx}][uom]" class="form-control uom-input text-center" value="PCS"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_amount]" class="form-control discount-input text-end" value="0" placeholder="0"></td>
                <td><div class="amt-display amount-display">0.00</div></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            </tr>`;
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
            updateSlNumbers();
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#itemsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            }
        });

        $(document).on('input', '.qty-input, .rate-input, .discount-input', function() {
            calculateRow($(this).closest('tr'));
        });
    });
</script>
@endsection
