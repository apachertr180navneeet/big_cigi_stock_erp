@extends('admin.layouts.app')

@section('style')
<style>
    .bill-table {
        min-width: 100%;
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
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sales /</span> View Bill</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sale Bill: {{ $sale->bill_no }}</h5>
            <div>
                <a href="{{ route('admin.sales.invoice', $sale->id) }}" class="btn btn-sm btn-primary me-2" target="_blank">
                    <i class="bx bx-printer me-1"></i> Print Invoice
                </a>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Customer:</strong> {{ $sale->customer->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Bill Date:</strong> {{ date('d-M-Y', strtotime($sale->bill_date)) }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-2"><strong>Status:</strong> <span class="badge-success-glow">{{ strtoupper($sale->status) }}</span></p>
                    <p class="mb-0"><strong>Net Payable:</strong> <span class="fs-5 fw-bold text-primary">₹ {{ number_format($sale->net_payable, 2) }}</span></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle bill-table">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Product Description</th>
                            <th style="min-width: 90px;">QTY</th>
                            <th style="min-width: 110px;">Rate</th>
                            <th class="computed-head" style="min-width: 130px;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalPackages = 0;
                            $totalQty = 0;
                            $totalFree = 0;
                            $totalBasic = 0;
                            $totalDiscount = 0;
                            $totalOtherDisc = 0;
                            $totalNet = 0;
                            $totalPackets = 0;
                            $totalTaxable = 0;
                            $totalCGST = 0;
                            $totalSGST = 0;
                            $totalTaxAmt = 0;
                        @endphp
                        @foreach($sale->items as $sItem)
                            @php
                                $basicAmt = $sItem->quantity * $sItem->rate;
                                $netAmt = $basicAmt - $sItem->discount_amount - $sItem->other_discount;
                                $totalPackages += $sItem->no_of_package;
                                $totalQty += $sItem->quantity;
                                $totalFree += $sItem->free_qty;
                                $totalBasic += $basicAmt;
                                $totalDiscount += $sItem->discount_amount;
                                $totalOtherDisc += $sItem->other_discount;
                                $totalNet += $netAmt;
                                $totalPackets += $sItem->packets;
                                $totalTaxable += $sItem->taxable_value;
                                $totalCGST += $sItem->cgst_amount;
                                $totalSGST += $sItem->sgst_amount;
                                $totalTaxAmt += $sItem->tax_amount;
                            @endphp
                            <tr>
                                <td>{{ $sItem->item->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($sItem->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($sItem->rate, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($sItem->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($totalQty, 2) }}</td>
                            <td></td>
                            <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Bill Summary --}}
            <div class="row mt-4">
                <div class="col-md-7">
                    <p class="text-muted mb-1"><strong>No. of Items Sold:</strong> {{ $sale->items->count() }}</p>
                </div>
                <div class="col-md-5">
                    <div class="bill-summary-card">
                        <div class="summary-row">
                            <span class="label">Items Subtotal</span>
                            <span class="value">₹ {{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Discount</span>
                            <span class="value">₹ {{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">40% Cess</span>
                            <span class="value">₹ {{ number_format($sale->cess_amount, 2) }}</span>
                        </div>
                        <div class="summary-row total-row">
                            <span>Net Amt Payable</span>
                            <span>₹ {{ number_format($sale->net_payable, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
