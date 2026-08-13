@extends('admin.layouts.app')

@section('style')
<style>
    .bill-table {
        min-width: 1400px;
    }
    .bill-table thead th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        padding: 10px 6px;
        background: #f5f5f9;
    }
    .bill-table thead .computed-head {
        background: #eceeff !important;
        color: #696cff;
    }
    .bill-table tbody td {
        font-size: 0.875rem;
        padding: 8px 6px;
        vertical-align: middle;
    }
    .bill-table tbody td.text-end {
        font-variant-numeric: tabular-nums;
    }
    .bill-table tfoot td {
        padding: 10px 6px;
        border-top: 2px solid #d9dee3;
        font-weight: 600;
    }
    .badge-success-glow {
        background-color: #e8fadf;
        color: #71dd37;
        font-weight: 600;
        padding: 0.5rem 0.75rem;
        border-radius: 5px;
    }

    /* Print Tax Invoice Layout (Tally / ITC Invoice Style) */
    @media print {
        body * {
            visibility: hidden;
        }
        #printableInvoice, #printableInvoice * {
            visibility: visible;
        }
        #printableInvoice {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 15px;
            background: #fff;
            color: #000;
            font-family: Arial, sans-serif;
        }
        .no-print {
            display: none !important;
        }
    }

    .tally-invoice-card {
        border: 1px solid #000;
        background: #fff;
        color: #000;
        font-family: Arial, sans-serif;
        font-size: 13px;
    }
    .tally-invoice-header {
        border-bottom: 1px solid #000;
    }
    .tally-box {
        border-right: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 6px 10px;
    }
    .tally-box:last-child {
        border-right: none;
    }
    .tally-table {
        width: 100%;
        border-collapse: collapse;
    }
    .tally-table th, .tally-table td {
        border: 1px solid #000;
        padding: 5px 8px;
        font-size: 12px;
    }
    .tally-table th {
        background: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="fw-bold m-0"><span class="text-muted fw-light">Purchases /</span> View Bill</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print Tax Invoice
            </button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- ERP Detailed Table View -->
    <div class="card mb-4 no-print">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase Bill: {{ $purchase->bill_no }}</h5>
            <span class="badge-success-glow">{{ strtoupper($purchase->status) }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Vendor:</strong> {{ $purchase->vendor->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Bill Date:</strong> {{ date('d-M-Y', strtotime($purchase->bill_date)) }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0"><strong>Total Amount:</strong> <span class="fs-4 fw-bold text-primary">₹ {{ number_format($purchase->total_amount, 2) }}</span></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle bill-table">
                    <thead>
                        <tr>
                            <th class="auto-head" style="min-width: 90px;">HSN Code</th>
                            <th class="auto-head" style="min-width: 90px;">Brand Code</th>
                            <th style="min-width: 220px;">Product Description</th>
                            <th style="min-width: 100px;">No. of Package</th>
                            <th style="min-width: 80px;">UOM</th>
                            <th style="min-width: 90px;">QTY</th>
                            <th style="min-width: 100px;">Rate</th>
                            <th class="computed-head" style="min-width: 110px;">Amount</th>
                            <th style="min-width: 90px;">Discount Amt</th>
                            <th class="computed-head" style="min-width: 110px;">Net Amount</th>
                            <th style="min-width: 90px;">Retail Packs</th>
                            <th style="min-width: 90px;">MRP</th>
                            <th class="computed-head" style="min-width: 110px;">Value for GST</th>
                            <th style="min-width: 70px;">CGST %</th>
                            <th class="computed-head" style="min-width: 100px;">CGST Amt</th>
                            <th style="min-width: 70px;">SGST %</th>
                            <th class="computed-head" style="min-width: 100px;">SGST Amt</th>
                            <th class="computed-head" style="min-width: 120px;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalPackages = 0;
                            $totalQty = 0;
                            $totalBasic = 0;
                            $totalDiscount = 0;
                            $totalNet = 0;
                            $totalPackets = 0;
                            $totalTV = 0;
                            $totalTaxable = 0;
                            $totalCGST = 0;
                            $totalSGST = 0;
                        @endphp
                        @foreach($purchase->items as $pItem)
                            @php
                                $basicAmt = $pItem->quantity * $pItem->rate;
                                $netAmt = $basicAmt - $pItem->discount_amount;
                                $tv = $pItem->packets * $pItem->mrp;
                                $totalPackages += $pItem->no_of_package;
                                $totalQty += $pItem->quantity;
                                $totalBasic += $basicAmt;
                                $totalDiscount += $pItem->discount_amount;
                                $totalNet += $netAmt;
                                $totalPackets += $pItem->packets;
                                $totalTV += $tv;
                                $totalTaxable += $pItem->taxable_value;
                                $totalCGST += $pItem->cgst_amount;
                                $totalSGST += $pItem->sgst_amount;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $pItem->item->hsn ?? '-' }}</td>
                                <td class="text-center">{{ $pItem->item->brand_code ?? '-' }}</td>
                                <td>{{ $pItem->item->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($pItem->no_of_package, 2) }}</td>
                                <td class="text-center">{{ $pItem->uom ?? 'PCS' }}</td>
                                <td class="text-end">{{ number_format($pItem->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->rate, 2) }}</td>
                                <td class="text-end">{{ number_format($basicAmt, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->discount_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($netAmt, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->packets, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->mrp, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->taxable_value, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->cgst_rate, 2) }}%</td>
                                <td class="text-end">{{ number_format($pItem->cgst_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($pItem->sgst_rate, 2) }}%</td>
                                <td class="text-end">{{ number_format($pItem->sgst_amount, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($pItem->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($totalPackages, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totalQty, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totalBasic, 2) }}</td>
                            <td class="text-end">{{ number_format($totalDiscount, 2) }}</td>
                            <td class="text-end">{{ number_format($totalNet, 2) }}</td>
                            <td class="text-end">{{ number_format($totalPackets, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totalTaxable, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totalCGST, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($totalSGST, 2) }}</td>
                            <td class="text-end">{{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tally / ITC Style Tax Invoice Layout (For View & Printing) -->
    @php
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
        $amountInWords = numberToWordsIndian($purchase->total_amount);
    @endphp

    <div class="card tally-invoice-card p-0 mb-4" id="printableInvoice">
        <div class="text-center py-2 border-bottom fw-bold" style="background:#f9f9f9; font-size:14px; text-transform:uppercase; letter-spacing:1px;">
            GST Tax Invoice / Purchase Bill
        </div>
        <div class="row m-0 tally-invoice-header">
            <!-- Left Header: Consignee & Supplier -->
            <div class="col-md-7 p-0 border-end">
                <div class="tally-box">
                    <strong>BIG BITE COMPANY (ITC)</strong><br>
                    Plot No. 250, Kirti Nagar, D-Balaji Mandir<br>
                    Gali, Rajiv Gandhi Colony, Ram Nagar,<br>
                    Jodhpur, Rajasthan, 342006<br>
                    <strong>GSTIN/UIN:</strong> 08BUIPS0691C1ZI<br>
                    <strong>State Name:</strong> Rajasthan, <strong>Code:</strong> 08
                </div>
                <div class="tally-box" style="background:#fafafa;">
                    <span class="text-muted" style="font-size:11px;">Consignee (Ship to)</span><br>
                    <strong>BIG BITE COMPANY (ITC)</strong><br>
                    Plot No. 250, Kirti Nagar, D-Balaji Mandir,<br>
                    Jodhpur, Rajasthan, 342006<br>
                    <strong>GSTIN/UIN:</strong> 08BUIPS0691C1ZI | <strong>State:</strong> Rajasthan (08)
                </div>
                <div class="tally-box" style="border-bottom:none;">
                    <span class="text-muted" style="font-size:11px;">Supplier (Bill from)</span><br>
                    <strong>{{ $purchase->vendor->name ?? 'I T C Limited' }}</strong><br>
                    {{ $purchase->vendor->address ?? 'Office No. 201, 2nd Floor, Durlabh Chambers, Jaipur' }}<br>
                    <strong>GSTIN/UIN:</strong> {{ $purchase->vendor->gst_number ?? $purchase->vendor->gstin ?? '08AAACI5950L2Z9' }}<br>
                    <strong>State Name:</strong> Rajasthan, <strong>Code:</strong> 08
                </div>
            </div>
            <!-- Right Header: Invoice Metadata -->
            <div class="col-md-5 p-0">
                <div class="row m-0">
                    <div class="col-6 tally-box">
                        <span class="text-muted" style="font-size:11px;">Invoice No.</span><br>
                        <strong>{{ $purchase->bill_no }}</strong>
                    </div>
                    <div class="col-6 tally-box">
                        <span class="text-muted" style="font-size:11px;">Dated</span><br>
                        <strong>{{ date('d-M-y', strtotime($purchase->bill_date)) }}</strong>
                    </div>
                </div>
                <div class="row m-0">
                    <div class="col-12 tally-box">
                        <span class="text-muted" style="font-size:11px;">Supplier Invoice No. & Date</span><br>
                        dt. {{ date('d-M-y', strtotime($purchase->bill_date)) }}
                    </div>
                </div>
                <div class="row m-0">
                    <div class="col-12 tally-box" style="border-bottom:none; min-height:85px;">
                        <span class="text-muted" style="font-size:11px;">Other References</span><br>
                        -
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="tally-table border-0 mb-0">
            <thead>
                <tr>
                    <th style="width:5%;">Sl No.</th>
                    <th style="width:45%; text-align:left;">Description of Goods</th>
                    <th style="width:12%; text-align:right;">Quantity</th>
                    <th style="width:10%; text-align:right;">Rate</th>
                    <th style="width:8%;">per</th>
                    <th style="width:8%; text-align:right;">Disc. %</th>
                    <th style="width:12%; text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $pItem)
                    @php
                        $bAmt = $pItem->quantity * $pItem->rate;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $pItem->item->name ?? 'N/A' }}</strong></td>
                        <td class="text-end">{{ number_format($pItem->quantity, 2) }} {{ $pItem->uom ?? 'PCS' }}</td>
                        <td class="text-end">{{ number_format($pItem->rate, 2) }}</td>
                        <td class="text-center">{{ $pItem->uom ?? 'PCS' }}</td>
                        <td class="text-end">{{ $pItem->discount_amount > 0 ? number_format(($pItem->discount_amount / ($bAmt ?: 1)) * 100, 2) . '%' : '' }}</td>
                        <td class="text-end">{{ number_format($bAmt, 2) }}</td>
                    </tr>
                @endforeach

                <!-- Summary / Tax Rows -->
                @if($totalDiscount > 0)
                <tr>
                    <td></td>
                    <td class="text-end font-italic"><em>Less: Discount Allowed</em></td>
                    <td colspan="4"></td>
                    <td class="text-end text-danger">-{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                @endif
                @if($totalSGST > 0)
                <tr>
                    <td></td>
                    <td class="text-end"><strong>SGST</strong></td>
                    <td colspan="4"></td>
                    <td class="text-end">{{ number_format($totalSGST, 2) }}</td>
                </tr>
                @endif
                @if($totalCGST > 0)
                <tr>
                    <td></td>
                    <td class="text-end"><strong>CGST</strong></td>
                    <td colspan="4"></td>
                    <td class="text-end">{{ number_format($totalCGST, 2) }}</td>
                </tr>
                @endif

                <!-- Total Row -->
                <tr style="background:#f9f9f9; font-weight:bold;">
                    <td colspan="2" class="text-end">Total</td>
                    <td class="text-end">{{ number_format($totalQty, 2) }} PCS</td>
                    <td colspan="3"></td>
                    <td class="text-end fs-6">₹ {{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Amount in Words & Footer Signatory -->
        <div class="row m-0 border-top" style="border-color:#000 !important;">
            <div class="col-md-7 p-2 border-end">
                <span class="text-muted" style="font-size:11px;">Amount Chargeable (in words)</span><br>
                <strong>{{ $amountInWords }}</strong><br><br>
                <span class="text-muted" style="font-size:11px;">Company's GSTIN/UIN:</span> <strong>08BUIPS0691C1ZI</strong>
            </div>
            <div class="col-md-5 p-2 text-end d-flex flex-column justify-content-between" style="min-height:100px;">
                <div class="text-muted" style="font-size:11px;">for {{ $purchase->vendor->name ?? 'I T C Limited' }}</div>
                <div class="fw-bold" style="font-size:12px;">Authorised Signatory</div>
            </div>
        </div>
    </div>
</div>
@endsection
