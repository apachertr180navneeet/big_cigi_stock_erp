@extends('admin.layouts.app')

@section('style')
<style>
    .invoice-wrapper {
        max-width: 1050px;
        margin: 0 auto;
    }
    .invoice-card {
        background: #fff;
        border: 1px solid #e0e4ec;
        border-radius: 10px;
        box-shadow: 0 4px 24px rgba(34, 41, 47, 0.08);
        overflow: hidden;
    }
    .invoice-header-bar {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 1.25rem 1.75rem;
    }
    .invoice-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #384551;
        letter-spacing: 0.5px;
    }
    .party-card {
        background: #fbfcfd;
        border: 1px solid #e7ecf2;
        border-radius: 8px;
        padding: 1.25rem;
        height: 100%;
    }
    .party-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #7b8a97;
        margin-bottom: 0.4rem;
    }
    .party-name {
        font-size: 1rem;
        font-weight: 700;
        color: #2c384a;
        margin-bottom: 0.25rem;
    }
    .party-detail {
        font-size: 0.85rem;
        color: #5d6d7e;
        line-height: 1.4;
    }
    .gstin-badge {
        display: inline-block;
        background: #e8f0fe;
        color: #1a73e8;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 2px 8px;
        border-radius: 4px;
        margin-top: 4px;
    }
    .meta-box {
        background: #ffffff;
        border: 1px solid #e7ecf2;
        border-radius: 8px;
        padding: 1rem 1.25rem;
    }
    .meta-item {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        font-size: 0.85rem;
        border-bottom: 1px dashed #edf2f7;
    }
    .meta-item:last-child {
        border-bottom: none;
    }
    .meta-label {
        color: #7b8a97;
    }
    .meta-value {
        font-weight: 600;
        color: #2c384a;
    }
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
    }
    .invoice-table thead th {
        background: #f1f4f9;
        color: #495867;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 2px solid #cbd5e1;
    }
    .invoice-table tbody td {
        padding: 9px 12px;
        font-size: 0.875rem;
        color: #334155;
        border-bottom: 1px solid #edf2f7;
    }
    .invoice-table tbody tr:hover {
        background: #f8fafc;
    }
    .invoice-table tfoot td {
        padding: 10px 12px;
        font-weight: 700;
        border-top: 2px solid #cbd5e1;
        background: #f8fafc;
    }
    .summary-box {
        width: 100%;
        max-width: 400px;
        margin-left: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 16px;
        font-size: 0.88rem;
        border-bottom: 1px solid #edf2f7;
    }
    .summary-row.total-row {
        background: #eef2ff;
        border-top: 2px solid #6366f1;
        border-bottom: none;
        padding: 12px 16px;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e1b4b;
    }
    .amount-words-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #6366f1;
        border-radius: 6px;
        padding: 12px 16px;
    }
    .signatory-box {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 14px;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: right;
        background: #fafbfc;
    }

    @media print {
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .no-print {
            display: none !important;
        }
        .container-xxl, .content-wrapper, .layout-page, .layout-wrapper, .layout-container {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .invoice-card {
            box-shadow: none !important;
            border: 1px solid #999 !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Top Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Purchases /</span> Bill #{{ $purchase->bill_no }}</h4>
            <span class="text-muted small">Generated on {{ date('d M, Y', strtotime($purchase->created_at)) }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Edit Purchase
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Tax Invoice
            </button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </div>

    @php
        $totalQty = 0;
        $subtotal = 0;
        foreach($purchase->items as $pItem) {
            $totalQty += $pItem->quantity;
            $subtotal += round($pItem->quantity * $pItem->rate, 2);
        }
        $discountAllowed = floatval($purchase->discount_allowed ?? 0);
        $sgstTotal = floatval($purchase->sgst_total ?? 0);
        $cgstTotal = floatval($purchase->cgst_total ?? 0);
        $grandTotal = floatval($purchase->total_amount);

        if (!function_exists('numberToWordsIndian')) {
            function numberToWordsIndian($number) {
                $no = floor($number);
                $point = round($number - $no, 2) * 100;
                $hundred = null;
                $digits_1 = strlen($no);
                $i = 0;
                $str = array();
                $words = array('0' => '', '1' => 'One', '2' => 'Two',
                    '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
                    '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
                    '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
                    '13' => 'Thirteen', '14' => 'Fourteen',
                    '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
                    '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
                    '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
                    '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty',
                    '90' => 'Ninety');
                $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
                while ($i < $digits_1) {
                    $divider = ($i == 2) ? 10 : 100;
                    $number = floor($no % $divider);
                    $no = floor($no / $divider);
                    $i += ($divider == 10) ? 1 : 2;
                    if ($number) {
                        $plural = (($counter = count($str)) && $number > 9) ? 's' : '';
                        $hundred = ($counter == 1 && isset($str[0]) && $str[0]) ? ' and ' : '';
                        $str [] = ($number < 20) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                            : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
                    } else $str[] = null;
                }
                $rupees = implode('', array_reverse($str));
                $paise = ($point > 0) ? " and " . ($words[floor($point / 10) * 10] . " " . $words[$point % 10]) . ' Paise' : '';
                return ($rupees ? "INR " . trim($rupees) : "INR Zero") . $paise . " Only";
            }
        }
        $amountInWords = numberToWordsIndian($grandTotal);
    @endphp

    <div class="invoice-wrapper">
        <div class="invoice-card" id="printableInvoice">
            <!-- Invoice Header Bar -->
            <div class="invoice-header-bar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt fs-3"></i>
                    </div>
                    <div>
                        <div class="invoice-title">Tax Invoice</div>
                        <span class="text-muted small">Original for Recipient</span>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                        <i class="bx bx-check-circle me-1"></i> {{ strtoupper($purchase->status ?? 'COMPLETED') }}
                    </span>
                    <div class="text-muted small mt-1">Invoice: <strong>{{ $purchase->bill_no }}</strong></div>
                </div>
            </div>

            <div class="p-4">
                <!-- Parties Info & Invoice Meta -->
                <div class="row g-3 mb-4">
                    <!-- Supplier Details -->
                    <div class="col-md-4">
                        <div class="party-card">
                            <div class="party-title"><i class="bx bx-store me-1 text-primary"></i> Supplier (Bill From)</div>
                            <div class="party-name">{{ $purchase->vendor->name ?? 'I T C Limited' }}</div>
                            <div class="party-detail">
                                {{ $purchase->vendor->address ?? 'Office No. 201, 2nd Floor, Durlabh Chambers, Jaipur' }}
                            </div>
                            <div class="mt-2">
                                <span class="gstin-badge">
                                    <strong>GSTIN:</strong> {{ $purchase->vendor->gst_number ?? $purchase->vendor->gstin ?? '08AAACI5950L2Z9' }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1">State: Rajasthan (08)</div>
                        </div>
                    </div>

                    <!-- Buyer Details -->
                    <div class="col-md-4">
                        <div class="party-card">
                            <div class="party-title"><i class="bx bx-building me-1 text-primary"></i> Consignee (Ship To)</div>
                            <div class="party-name">BIG BITE COMPANY (ITC)</div>
                            <div class="party-detail">
                                Plot No. 250, Kirti Nagar, D-Balaji Mandir Gali, Rajiv Gandhi Colony, Ram Nagar, Jodhpur, Rajasthan - 342006
                            </div>
                            <div class="mt-2">
                                <span class="gstin-badge">
                                    <strong>GSTIN:</strong> 08BUIPS0691C1ZI
                                </span>
                            </div>
                            <div class="text-muted small mt-1">State: Rajasthan (08)</div>
                        </div>
                    </div>

                    <!-- Invoice Metadata -->
                    <div class="col-md-4">
                        <div class="meta-box">
                            <div class="meta-item">
                                <span class="meta-label">Invoice No.</span>
                                <span class="meta-value text-primary fw-bold">{{ $purchase->bill_no }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Dated</span>
                                <span class="meta-value">{{ date('d-M-Y', strtotime($purchase->bill_date)) }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Supplier Inv No.</span>
                                <span class="meta-value">{{ $purchase->supplier_invoice_no ?? '-' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Supplier Inv Date</span>
                                <span class="meta-value">{{ $purchase->supplier_invoice_date ? date('d-M-Y', strtotime($purchase->supplier_invoice_date)) : '-' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">e-Way Bill No.</span>
                                <span class="meta-value">{{ $purchase->eway_bill_no ?? '-' }}</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Other Ref.</span>
                                <span class="meta-value">{{ $purchase->other_references ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4 rounded border">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">Sl No.</th>
                                <th style="width: 40%;">Description of Goods</th>
                                <th style="width: 13%;" class="text-end">Quantity</th>
                                <th style="width: 12%;" class="text-end">Rate</th>
                                <th style="width: 8%;" class="text-center">per</th>
                                <th style="width: 8%;" class="text-center">Disc. %</th>
                                <th style="width: 14%;" class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $idx => $pItem)
                                @php
                                    $lineAmt = round($pItem->quantity * $pItem->rate, 2);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $pItem->item->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($pItem->quantity, 2) }}</td>
                                    <td class="text-end">{{ number_format($pItem->rate, 2) }}</td>
                                    <td class="text-center text-muted">{{ $pItem->uom ?? 'PCS' }}</td>
                                    <td class="text-center text-muted">-</td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($lineAmt, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end">Total Summary:</td>
                                <td class="text-end text-primary">{{ number_format($totalQty, 2) }} PCS</td>
                                <td colspan="3"></td>
                                <td class="text-end text-primary fs-6">{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Bottom Section: Amount in Words & Summary Box -->
                <div class="row g-3 align-items-start">
                    <div class="col-md-7">
                        <div class="amount-words-box mb-3">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Amount Chargeable (in words):</div>
                            <div class="fw-bold text-dark mt-1 fs-6">{{ $amountInWords }}</div>
                        </div>

                        <div class="signatory-box">
                            <div class="text-muted small">for <strong>{{ $purchase->vendor->name ?? 'I T C Limited' }}</strong></div>
                            <div class="fw-bold text-dark small">Authorised Signatory</div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="summary-box">
                            <div class="summary-row">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold text-dark">₹ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($discountAllowed > 0)
                            <div class="summary-row">
                                <span class="text-danger fw-semibold"><em>Less: Discount Allowed</em></span>
                                <span class="text-danger fw-bold">-₹ {{ number_format($discountAllowed, 2) }}</span>
                            </div>
                            @endif
                            @if($sgstTotal > 0)
                            <div class="summary-row">
                                <span class="text-muted">SGST</span>
                                <span class="fw-semibold text-dark">₹ {{ number_format($sgstTotal, 2) }}</span>
                            </div>
                            @endif
                            @if($cgstTotal > 0)
                            <div class="summary-row">
                                <span class="text-muted">CGST</span>
                                <span class="fw-semibold text-dark">₹ {{ number_format($cgstTotal, 2) }}</span>
                            </div>
                            @endif
                            <div class="summary-row total-row">
                                <span>₹ Grand Total</span>
                                <span class="fs-5">₹ {{ number_format($grandTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
