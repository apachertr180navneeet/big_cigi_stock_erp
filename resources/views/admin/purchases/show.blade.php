@extends('admin.layouts.app')

@section('style')
<style>
    .bill-table {
        min-width: 2100px;
    }
    .bill-table thead th {
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
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Purchases /</span> View Bill</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase Bill: {{ $purchase->bill_no }}</h5>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Vendor:</strong> {{ $purchase->vendor->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Bill Date:</strong> {{ date('d-M-Y', strtotime($purchase->bill_date)) }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-2"><strong>Status:</strong> <span class="badge-success-glow">{{ strtoupper($purchase->status) }}</span></p>
                    <p class="mb-0"><strong>Total Amount:</strong> <span class="fs-5 fw-bold text-primary">₹ {{ number_format($purchase->total_amount, 2) }}</span></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle bill-table">
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
                                <td class="text-center">{{ $pItem->uom ?? '-' }}</td>
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
</div>
@endsection
