@extends('admin.layouts.app')

@section('style')
<style>
    #itemsTable {
        min-width: 2100px;
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
    #itemsTable .package-input,
    #itemsTable .packets-input,
    #itemsTable .mrp-input,
    #itemsTable .discount-input {
        min-width: 100px !important;
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
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Purchases /</span> Add Purchase</h4>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">New Purchase Bill</h5>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <form action="{{ route('admin.purchases.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
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
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                            <i class="bx bx-file me-1"></i> Import Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="addRow">
                            <i class="bx bx-plus me-1"></i> Add Item
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th class="auto-head" style="min-width: 90px;">HSN Code</th>
                                <th class="auto-head" style="min-width: 90px;">Brand Code</th>
                                <th style="min-width: 250px;">Product Description</th>
                                <th style="min-width: 110px;">No. of Package</th>
                                <th style="min-width: 90px;">UOM</th>
                                <th style="min-width: 100px;">QTY</th>
                                <th style="min-width: 110px;">Rate</th>
                                <th class="computed-head" style="min-width: 120px;">Amount</th>
                                <th style="min-width: 100px;">Discount Amt</th>
                                <th class="computed-head" style="min-width: 120px;">Net Amount</th>
                                <th style="min-width: 100px;">Retail Packs</th>
                                <th style="min-width: 100px;">MRP</th>
                                <th class="computed-head" style="min-width: 125px;">Value for GST</th>
                                <th style="min-width: 80px;">CGST %</th>
                                <th class="computed-head" style="min-width: 115px;">CGST Amt</th>
                                <th style="min-width: 80px;">SGST %</th>
                                <th class="computed-head" style="min-width: 115px;">SGST Amt</th>
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
                                <td><input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                                <td class="computed-col"><div class="computed-cell basic-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][discount_amount]" class="form-control discount-input text-end" placeholder="0.00" value="0.00"></td>
                                <td class="computed-col"><div class="computed-cell net-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][packets]" class="form-control packets-input text-end" required placeholder="0"></td>
                                <td><input type="number" step="0.01" name="items[0][mrp]" class="form-control mrp-input text-end" required placeholder="0.00"></td>
                                <td class="computed-col"><div class="computed-cell taxable-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][cgst_rate]" class="form-control cgst-rate-input text-end" value="20.00"></td>
                                <td class="computed-col"><div class="computed-cell cgst-amt-display">0.00</div></td>
                                <td><input type="number" step="0.01" name="items[0][sgst_rate]" class="form-control sgst-rate-input text-end" value="20.00"></td>
                                <td class="computed-col"><div class="computed-cell sgst-amt-display">0.00</div></td>
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
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalBasic">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalDiscount">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalNet">0.00</div></td>
                                <td><div class="computed-cell fw-bold" id="totalPackets">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalTaxable">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalCGST">0.00</div></td>
                                <td class="border-0"></td>
                                <td><div class="computed-cell fw-bold" id="totalSGST">0.00</div></td>
                                <td><div class="computed-cell total-cell fw-bold" id="grandTotal">0.00</div></td>
                                <td class="border-0"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-4">
                    <div>
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="grand-total-box">
                            <span class="label">Grand Total ₹</span>
                            <span class="value" id="grandTotalDisplay">0.00</span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Purchase
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Excel Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-file me-2 text-success"></i>Import Purchase Items from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="excelImportForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <small><i class="bx bx-info-circle me-1"></i> Upload your purchase bill Excel file (ITC invoice format or standard format). Item descriptions, quantities, rates, and amounts will be populated automatically into the table.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Select Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" id="excelFileInput" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.purchases.template') }}" class="btn btn-sm btn-link text-decoration-none px-0">
                            <i class="bx bx-download me-1"></i> Download Sample Excel Template
                        </a>
                    </div>
                    <div id="importAlert" class="mt-3 d-none alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="btnUploadExcel">
                        <i class="bx bx-upload me-1"></i> Upload & Import
                    </button>
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
            let discount = parseFloat(row.find('.discount-input').val()) || 0;
            let packets = parseFloat(row.find('.packets-input').val()) || 0;
            let mrp = parseFloat(row.find('.mrp-input').val()) || 0;
            let cgst_rate = parseFloat(row.find('.cgst-rate-input').val()) || 0;
            let sgst_rate = parseFloat(row.find('.sgst-rate-input').val()) || 0;

            let basic_value = Math.round((qty * rate) * 100) / 100;
            let net_amount = Math.round((basic_value - discount) * 100) / 100;
            let total_value = Math.round((packets * mrp) * 100) / 100;
            let taxable_value = Math.round((total_value / 1.40) * 100) / 100;
            let cgst_amount = Math.round((taxable_value * (cgst_rate / 100)) * 100) / 100;
            let sgst_amount = Math.round((taxable_value * (sgst_rate / 100)) * 100) / 100;
            let tax_amount = cgst_amount + sgst_amount;
            let amount = Math.round((net_amount + tax_amount) * 100) / 100;

            row.find('.basic-display').text(formatNum(basic_value)).data('val', basic_value);
            row.find('.net-display').text(formatNum(net_amount)).data('val', net_amount);
            row.find('.tv-display').text(formatNum(total_value)).data('val', total_value);
            row.find('.taxable-display').text(formatNum(taxable_value)).data('val', taxable_value);
            row.find('.cgst-amt-display').text(formatNum(cgst_amount)).data('val', cgst_amount);
            row.find('.sgst-amt-display').text(formatNum(sgst_amount)).data('val', sgst_amount);
            row.find('.amount-display').text(formatNum(amount)).data('val', amount);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let totalPackages = 0, totalQty = 0, totalDiscount = 0, totalPackets = 0;
            let totalBasic = 0, totalNet = 0, totalTV = 0, totalTaxable = 0, totalCGST = 0, totalSGST = 0, grandTotal = 0;

            $('.package-input').each(function() { totalPackages += (parseFloat($(this).val()) || 0); });
            $('.qty-input').each(function() { totalQty += (parseFloat($(this).val()) || 0); });
            $('.discount-input').each(function() { totalDiscount += (parseFloat($(this).val()) || 0); });
            $('.packets-input').each(function() { totalPackets += (parseFloat($(this).val()) || 0); });

            $('.basic-display').each(function() { totalBasic += ($(this).data('val') || 0); });
            $('.net-display').each(function() { totalNet += ($(this).data('val') || 0); });
            $('.tv-display').each(function() { totalTV += ($(this).data('val') || 0); });
            $('.taxable-display').each(function() { totalTaxable += ($(this).data('val') || 0); });
            $('.cgst-amt-display').each(function() { totalCGST += ($(this).data('val') || 0); });
            $('.sgst-amt-display').each(function() { totalSGST += ($(this).data('val') || 0); });
            $('.amount-display').each(function() { grandTotal += ($(this).data('val') || 0); });

            $('#totalPackages').text(formatNum(totalPackages));
            $('#totalQty').text(formatNum(totalQty));
            $('#totalDiscount').text(formatNum(totalDiscount));
            $('#totalPackets').text(formatNum(totalPackets));
            $('#totalBasic').text(formatNum(totalBasic));
            $('#totalNet').text(formatNum(totalNet));
            $('#totalTV').text(formatNum(totalTV));
            $('#totalTaxable').text(formatNum(totalTaxable));
            $('#totalCGST').text(formatNum(totalCGST));
            $('#totalSGST').text(formatNum(totalSGST));
            $('#grandTotal').text(formatNum(grandTotal));
            $('#grandTotalDisplay').text(formatNum(grandTotal));
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
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell basic-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_amount]" class="form-control discount-input text-end" placeholder="0.00" value="0.00"></td>
                <td class="computed-col"><div class="computed-cell net-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][packets]" class="form-control packets-input text-end" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][mrp]" class="form-control mrp-input text-end" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell taxable-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][cgst_rate]" class="form-control cgst-rate-input text-end" value="20.00"></td>
                <td class="computed-col"><div class="computed-cell cgst-amt-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][sgst_rate]" class="form-control sgst-rate-input text-end" value="20.00"></td>
                <td class="computed-col"><div class="computed-cell sgst-amt-display">0.00</div></td>
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

        $(document).on('input', '.package-input, .qty-input, .rate-input, .discount-input, .packets-input, .mrp-input, .cgst-rate-input, .sgst-rate-input', function() {
            let row = $(this).closest('tr');
            calculateRow(row);
        });

        $(document).on('change', '.item-select', function() {
            let row = $(this).closest('tr');
            populateItemInfo(row);
        });

        // Excel Import Handler
        $('#excelImportForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let btn = $('#btnUploadExcel');
            let alertBox = $('#importAlert');

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Parsing Excel...');
            alertBox.addClass('d-none').removeClass('alert-danger alert-success').text('');

            $.ajax({
                url: "{{ route('admin.purchases.parse_excel') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i> Upload & Import');
                    if (res.success) {
                        if (res.vendor_id && !$('select[name="vendor_id"]').val()) {
                            $('select[name="vendor_id"]').val(res.vendor_id);
                        }

                        if (res.items && res.items.length > 0) {
                            let firstRow = $('#itemsTable tbody tr:first');
                            let isFirstRowEmpty = $('#itemsTable tbody tr').length === 1 && !firstRow.find('.item-select').val();

                            if (isFirstRowEmpty) {
                                $('#itemsTable tbody').empty();
                            }

                            res.items.forEach(function(item) {
                                appendImportedRow(item);
                            });

                            $('#itemsTable tbody tr').each(function() {
                                calculateRow($(this));
                            });

                            $('#importExcelModal').modal('hide');
                            $('#excelImportForm')[0].reset();
                            alert(res.message);
                        }
                    } else {
                        alertBox.removeClass('d-none').addClass('alert-danger').text(res.message || 'Error parsing Excel.');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bx bx-upload me-1"></i> Upload & Import');
                    let msg = 'Import failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alertBox.removeClass('d-none').addClass('alert-danger').text(msg);
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
                itemData[item.item_id] = { hsn: item.hsn || '', brand_code: item.brand_code || '' };
            }

            let newRow = $(`<tr>
                <td><div class="auto-field hs-display">${item.hsn || '-'}</div></td>
                <td><div class="auto-field brand-display">${item.brand_code || '-'}</div></td>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>
                        ${options}
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][no_of_package]" class="form-control package-input text-end" value="${item.no_of_package || 0}" placeholder="0"></td>
                <td><input type="text" name="items[${rowIdx}][uom]" class="form-control uom-input text-center" value="${item.uom || 'PCS'}" placeholder="UOM"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input text-end" value="${item.quantity || 0}" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input text-end" value="${item.rate || 0}" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell basic-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][discount_amount]" class="form-control discount-input text-end" value="${item.discount_amount || 0}" placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell net-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][packets]" class="form-control packets-input text-end" value="${item.packets || 0}" required placeholder="0"></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][mrp]" class="form-control mrp-input text-end" value="${item.mrp || 0}" required placeholder="0.00"></td>
                <td class="computed-col"><div class="computed-cell taxable-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][cgst_rate]" class="form-control cgst-rate-input text-end" value="${item.cgst_rate || 20.00}"></td>
                <td class="computed-col"><div class="computed-cell cgst-amt-display">0.00</div></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][sgst_rate]" class="form-control sgst-rate-input text-end" value="${item.sgst_rate || 20.00}"></td>
                <td class="computed-col"><div class="computed-cell sgst-amt-display">0.00</div></td>
                <td class="computed-col"><div class="computed-cell total-cell amount-display">0.00</div></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            </tr>`);

            $('#itemsTable tbody').append(newRow);
            rowIdx++;
            populateItemInfo(newRow);
        }
    });
</script>
@endsection
