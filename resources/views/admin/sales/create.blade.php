@extends('admin.layouts.app')

@section('style')
<style>
    #itemsTable {
        min-width: 100%;
    }
    #itemsTable thead th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        padding: 12px 6px;
        background: #f5f5f9;
    }
    #itemsTable tbody td {
        padding: 6px 5px;
        vertical-align: middle;
    }
    #itemsTable .form-control,
    #itemsTable .form-select {
        font-size: 0.875rem;
        padding: 0.45rem 0.5rem;
        min-height: 38px;
    }
    #itemsTable .item-select {
        min-width: 280px !important;
    }
    #itemsTable .qty-input,
    #itemsTable .free-input,
    #itemsTable .package-input,
    #itemsTable .packets-input,
    #itemsTable .mrp-input,
    #itemsTable .discount-input,
    #itemsTable .disc-pct-input,
    #itemsTable .other-disc-input {
        min-width: 90px !important;
    }
    #itemsTable .rate-input {
        min-width: 110px !important;
    }
    #itemsTable .uom-input,
    #itemsTable .cgst-rate-input,
    #itemsTable .sgst-rate-input {
        min-width: 80px !important;
    }
    #itemsTable .form-control:focus {
        box-shadow: 0 0 0 0.15rem rgba(105, 108, 255, 0.2);
    }
    .auto-field {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
        padding: 0.45rem 0.5rem;
        background: #fafbfe;
        border-radius: 5px;
        color: #697a8d;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #d9dee3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .computed-cell {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: right;
        padding: 0.45rem 0.65rem;
        background: #f0f1ff;
        border-radius: 5px;
        color: #566a7f;
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        border: 1px solid #e3e4ff;
    }
    .computed-cell.total-cell {
        background: #e7f3ff;
        color: #2b5a8c;
        font-weight: 600;
        border-color: #c5dcf0;
    }
    #itemsTable thead .computed-head {
        background: #eceeff !important;
        color: #696cff;
    }
    #itemsTable thead .auto-head {
        background: #f0f5f0 !important;
        color: #71839b;
    }
    #itemsTable tfoot td {
        padding: 8px 4px;
        border-top: 2px solid #d9dee3;
    }
    .grand-total-box {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #696cff 0%, #5f61e6 100%);
        color: #fff;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 1rem;
    }
    .grand-total-box .label { font-weight: 400; opacity: 0.85; }
    .grand-total-box .value { font-weight: 700; font-size: 1.1rem; }
    .bill-summary-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px 20px;
    }
    .bill-summary-card .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 0.9rem;
    }
    .bill-summary-card .summary-row.total-row {
        border-top: 2px solid #696cff;
        margin-top: 8px;
        padding-top: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        color: #696cff;
    }
    .bill-summary-card .summary-row .label { color: #697a8d; }
    .bill-summary-card .summary-row .value { font-weight: 600; color: #566a7f; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sales /</span> Add Sale</h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">New Sale Bill</h5>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.sales.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bill No <span class="text-danger">*</span></label>
                        <input type="text" name="bill_no" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                        <input type="date" name="bill_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <hr>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0"><i class="bx bx-box me-1"></i> Items</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="addRow">
                        <i class="bx bx-plus me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="min-width: 250px;">Product Description</th>
                                <th style="min-width: 90px;">QTY</th>
                                <th style="min-width: 110px;">Rate</th>
                                <th class="computed-head" style="min-width: 130px;">Total Amount</th>
                                <th style="min-width: 60px;">Act</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input text-end" required placeholder="0"></td>
                                <td><input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                                <td class="computed-col"><div class="computed-cell total-cell amount-display">0.00</div></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end fw-bold" style="vertical-align: middle;">Total:</td>
                                <td><div class="computed-cell fw-bold" id="totalQty">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell total-cell fw-bold" id="grandTotal">0.00</div></td>
                                <td class="border-0"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Bill Summary Section --}}
                <div class="row mt-4">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="bill-summary-card">
                            <div class="summary-row">
                                <span class="label">Items Subtotal</span>
                                <span class="value" id="summarySubtotal">₹ 0.00</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">TCS u/s 206C(1H)</span>
                                <div style="width: 140px;">
                                    <input type="number" step="0.01" name="tcs_amount" class="form-control form-control-sm text-end" id="tcsInput" value="0.00">
                                </div>
                            </div>
                            <div class="summary-row">
                                <span class="label">Credit Adj</span>
                                <div style="width: 140px;">
                                    <input type="number" step="0.01" name="credit_adj" class="form-control form-control-sm text-end" id="creditAdjInput" value="0.00">
                                </div>
                            </div>
                            <div class="summary-row">
                                <span class="label">Round Off</span>
                                <div style="width: 140px;">
                                    <input type="number" step="0.01" name="round_off" class="form-control form-control-sm text-end" id="roundOffInput" value="0.00">
                                </div>
                            </div>
                            <div class="summary-row total-row">
                                <span>Net Amt Payable</span>
                                <span id="netPayableDisplay">₹ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-4">
                    <div>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="grand-total-box">
                            <span class="label">Net Payable ₹</span>
                            <span class="value" id="grandTotalDisplay">0.00</span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Sale
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let rowIdx = 1;

        // Item data for auto-populating HS Code & Brand Code
        let itemData = {};
        @foreach($items as $item)
            itemData[{{ $item->id }}] = {
                hsn: '{{ $item->hsn ?? "" }}',
                brand_code: '{{ $item->brand_code ?? "" }}'
            };
        @endforeach

        function formatNum(n) {
            return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function calculateRow(row) {
            let qty = parseFloat(row.find('.qty-input').val()) || 0;
            let rate = parseFloat(row.find('.rate-input').val()) || 0;

            let amount = Math.round((qty * rate) * 100) / 100;

            row.find('.amount-display').text(formatNum(amount)).data('val', amount);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let totalQty = 0, grandTotal = 0;

            $('.qty-input').each(function() { totalQty += (parseFloat($(this).val()) || 0); });
            $('.amount-display').each(function() { grandTotal += ($(this).data('val') || 0); });

            $('#totalQty').text(formatNum(totalQty));
            $('#grandTotal').text(formatNum(grandTotal));

            // Update summary section
            $('#summarySubtotal').text('₹ ' + formatNum(grandTotal));

            calculateNetPayable(grandTotal);
        }

        function calculateNetPayable(subtotal) {
            let tcs = parseFloat($('#tcsInput').val()) || 0;
            let creditAdj = parseFloat($('#creditAdjInput').val()) || 0;
            let roundOff = parseFloat($('#roundOffInput').val()) || 0;

            let netPayable = Math.round((subtotal + roundOff + tcs - creditAdj) * 100) / 100;

            $('#netPayableDisplay').text('₹ ' + formatNum(netPayable));
            $('#grandTotalDisplay').text(formatNum(netPayable));
        }

        // Auto-populate HS Code & Brand Code when item is selected
        function populateItemInfo(row) {
            let itemId = row.find('.item-select').val();
            if (itemId && itemData[itemId]) {
                row.find('.hs-display').text(itemData[itemId].hsn || '-');
                row.find('.brand-display').text(itemData[itemId].brand_code || '-');
            } else {
                row.find('.hs-display').text('-');
                row.find('.brand-display').text('-');
            }
        }

        $('#addRow').click(function() {
            let options = '<option value="">Select Item</option>';
            @foreach($items as $item)
                options += '<option value="{{ $item->id }}">{{ $item->name }}</option>';
            @endforeach
            let newRow = `<tr>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>
                        ${options}
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell total-cell amount-display">0.00</div></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            </tr>`;
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#itemsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            }
        });

        $(document).on('input', '.qty-input, .rate-input', function() {
            let row = $(this).closest('tr');
            calculateRow(row);
        });

        // Recalculate net payable when bill-level inputs change
        $(document).on('input', '#tcsInput, #creditAdjInput, #roundOffInput', function() {
            let subtotal = 0;
            $('.amount-display').each(function() { subtotal += ($(this).data('val') || 0); });
            calculateNetPayable(subtotal);
        });

        $(document).on('change', '.item-select', function() {
            let row = $(this).closest('tr');
            populateItemInfo(row);
        });
    });
</script>
@endsection
