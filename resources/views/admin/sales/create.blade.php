@extends('admin.layouts.app')

@section('style')
<style>
    #itemsTable {
        min-width: 2600px;
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
                                <th class="auto-head" style="min-width: 90px;">HSN Code</th>
                                <th class="auto-head" style="min-width: 90px;">Brand Code</th>
                                <th style="min-width: 250px;">Product Description</th>
                                <th style="min-width: 100px;">No. of Pkg</th>
                                <th style="min-width: 80px;">UOM</th>
                                <th style="min-width: 90px;">QTY</th>
                                <th style="min-width: 80px;">Free</th>
                                <th style="min-width: 110px;">Rate</th>
                                <th class="computed-head" style="min-width: 120px;">Gross Amt</th>
                                <th style="min-width: 80px;">Disc%</th>
                                <th style="min-width: 100px;">Disc Amt</th>
                                <th style="min-width: 100px;">Other Disc</th>
                                <th class="computed-head" style="min-width: 120px;">Net Amount</th>
                                <th style="min-width: 100px;">Retail Packs</th>
                                <th style="min-width: 100px;">MRP</th>
                                <th class="computed-head" style="min-width: 125px;">Value for GST</th>
                                <th style="min-width: 80px;">CGST %</th>
                                <th class="computed-head" style="min-width: 115px;">CGST Amt</th>
                                <th style="min-width: 80px;">SGST %</th>
                                <th class="computed-head" style="min-width: 115px;">SGST Amt</th>
                                <th class="computed-head" style="min-width: 115px;">Tot. Tax</th>
                                <th class="computed-head" style="min-width: 130px;">Total Amount</th>
                                <th style="min-width: 60px;">Act</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><div class="auto-field hs-display">-</div></td>
                                <td><div class="auto-field brand-display">-</div></td>
                                <td>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" name="items[0][no_of_package]" class="form-control package-input text-end" placeholder="0"></td>
                                <td><input type="text" name="items[0][uom]" class="form-control uom-input text-center" placeholder="UOM"></td>
                                <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input text-end" required placeholder="0"></td>
                                <td><input type="number" step="0.01" name="items[0][free_qty]" class="form-control free-input text-end" placeholder="0" value="0"></td>
                                <td><input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                                <td class="computed-col"><div class="computed-cell basic-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][discount_percent]" class="form-control disc-pct-input text-end" placeholder="0" value="0"></td>
                                <td><input type="number" step="0.01" name="items[0][discount_amount]" class="form-control discount-input text-end" placeholder="0.00" value="0.00"></td>
                                <td><input type="number" step="0.01" name="items[0][other_discount]" class="form-control other-disc-input text-end" placeholder="0.00" value="0.00"></td>
                                <td class="computed-col"><div class="computed-cell net-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][packets]" class="form-control packets-input text-end" required placeholder="0"></td>
                                <td><input type="number" step="0.01" name="items[0][mrp]" class="form-control mrp-input text-end" required placeholder="0.00"></td>
                                <td class="computed-col"><div class="computed-cell taxable-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][cgst_rate]" class="form-control cgst-rate-input text-end" value="20.00"></td>
                                <td class="computed-col"><div class="computed-cell cgst-amt-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][sgst_rate]" class="form-control sgst-rate-input text-end" value="20.00"></td>
                                <td class="computed-col"><div class="computed-cell sgst-amt-display">0.00</div></td>
                                <td class="computed-col"><div class="computed-cell tax-display">0.00</div></td>
                                <td class="computed-col"><div class="computed-cell total-cell amount-display">0.00</div></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold" style="vertical-align: middle;">Total:</td>
                                <td><div class="computed-cell fw-bold" id="totalPackages">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalQty">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalFree">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalBasic">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalDiscount">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalOtherDisc">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalNet">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalPackets">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalTaxable">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalCGST">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalSGST">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalTax">0.00</div></td>
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
            let disc_pct = parseFloat(row.find('.disc-pct-input').val()) || 0;
            let disc_amt = parseFloat(row.find('.discount-input').val()) || 0;
            let other_disc = parseFloat(row.find('.other-disc-input').val()) || 0;
            let packets = parseFloat(row.find('.packets-input').val()) || 0;
            let mrp = parseFloat(row.find('.mrp-input').val()) || 0;
            let cgst_rate = parseFloat(row.find('.cgst-rate-input').val()) || 0;
            let sgst_rate = parseFloat(row.find('.sgst-rate-input').val()) || 0;

            let basic_value = Math.round((qty * rate) * 100) / 100;

            // If disc% is given and disc_amt is 0, calculate disc_amt from percentage
            if (disc_pct > 0 && disc_amt === 0) {
                disc_amt = Math.round((basic_value * (disc_pct / 100)) * 100) / 100;
                row.find('.discount-input').val(disc_amt.toFixed(2));
            }

            let net_amount = Math.round((basic_value - disc_amt - other_disc) * 100) / 100;
            let total_value = Math.round((packets * mrp) * 100) / 100;
            let taxable_value = Math.round((total_value / 1.40) * 100) / 100;
            let cgst_amount = Math.round((taxable_value * (cgst_rate / 100)) * 100) / 100;
            let sgst_amount = Math.round((taxable_value * (sgst_rate / 100)) * 100) / 100;
            let tax_amount = cgst_amount + sgst_amount;
            let amount = Math.round((net_amount + tax_amount) * 100) / 100;

            row.find('.basic-display').text(formatNum(basic_value)).data('val', basic_value);
            row.find('.net-display').text(formatNum(net_amount)).data('val', net_amount);
            row.find('.taxable-display').text(formatNum(taxable_value)).data('val', taxable_value);
            row.find('.cgst-amt-display').text(formatNum(cgst_amount)).data('val', cgst_amount);
            row.find('.sgst-amt-display').text(formatNum(sgst_amount)).data('val', sgst_amount);
            row.find('.tax-display').text(formatNum(tax_amount)).data('val', tax_amount);
            row.find('.amount-display').text(formatNum(amount)).data('val', amount);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let totalPackages = 0, totalQty = 0, totalFree = 0, totalDiscount = 0, totalOtherDisc = 0, totalPackets = 0;
            let totalBasic = 0, totalNet = 0, totalTaxable = 0, totalCGST = 0, totalSGST = 0, totalTaxAmt = 0, grandTotal = 0;

            $('.package-input').each(function() { totalPackages += (parseFloat($(this).val()) || 0); });
            $('.qty-input').each(function() { totalQty += (parseFloat($(this).val()) || 0); });
            $('.free-input').each(function() { totalFree += (parseFloat($(this).val()) || 0); });
            $('.discount-input').each(function() { totalDiscount += (parseFloat($(this).val()) || 0); });
            $('.other-disc-input').each(function() { totalOtherDisc += (parseFloat($(this).val()) || 0); });
            $('.packets-input').each(function() { totalPackets += (parseFloat($(this).val()) || 0); });

            $('.basic-display').each(function() { totalBasic += ($(this).data('val') || 0); });
            $('.net-display').each(function() { totalNet += ($(this).data('val') || 0); });
            $('.taxable-display').each(function() { totalTaxable += ($(this).data('val') || 0); });
            $('.cgst-amt-display').each(function() { totalCGST += ($(this).data('val') || 0); });
            $('.sgst-amt-display').each(function() { totalSGST += ($(this).data('val') || 0); });
            $('.tax-display').each(function() { totalTaxAmt += ($(this).data('val') || 0); });
            $('.amount-display').each(function() { grandTotal += ($(this).data('val') || 0); });

            $('#totalPackages').text(formatNum(totalPackages));
            $('#totalQty').text(formatNum(totalQty));
            $('#totalFree').text(formatNum(totalFree));
            $('#totalDiscount').text(formatNum(totalDiscount));
            $('#totalOtherDisc').text(formatNum(totalOtherDisc));
            $('#totalPackets').text(formatNum(totalPackets));
            $('#totalBasic').text(formatNum(totalBasic));
            $('#totalNet').text(formatNum(totalNet));
            $('#totalTaxable').text(formatNum(totalTaxable));
            $('#totalCGST').text(formatNum(totalCGST));
            $('#totalSGST').text(formatNum(totalSGST));
            $('#totalTax').text(formatNum(totalTaxAmt));
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
                <td><div class="auto-field hs-display">-</div></td>
                <td><div class="auto-field brand-display">-</div></td>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>
                        ${options}
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][no_of_package]" class="form-control package-input text-end" placeholder="0"></td>
                <td><input type="text" name="items[${rowIdx}][uom]" class="form-control uom-input text-center" placeholder="UOM"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][free_qty]" class="form-control free-input text-end" placeholder="0" value="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell basic-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_percent]" class="form-control disc-pct-input text-end" placeholder="0" value="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_amount]" class="form-control discount-input text-end" placeholder="0.00" value="0.00"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][other_discount]" class="form-control other-disc-input text-end" placeholder="0.00" value="0.00"></td>
                <td class="computed-col"><div class="computed-cell net-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][packets]" class="form-control packets-input text-end" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][mrp]" class="form-control mrp-input text-end" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell taxable-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][cgst_rate]" class="form-control cgst-rate-input text-end" value="20.00"></td>
                <td class="computed-col"><div class="computed-cell cgst-amt-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][sgst_rate]" class="form-control sgst-rate-input text-end" value="20.00"></td>
                <td class="computed-col"><div class="computed-cell sgst-amt-display">0.00</div></td>
                <td class="computed-col"><div class="computed-cell tax-display">0.00</div></td>
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

        $(document).on('input', '.package-input, .qty-input, .free-input, .rate-input, .disc-pct-input, .discount-input, .other-disc-input, .packets-input, .mrp-input, .cgst-rate-input, .sgst-rate-input', function() {
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
