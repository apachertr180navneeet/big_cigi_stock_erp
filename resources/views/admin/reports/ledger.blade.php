@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Reports /</span> Stock Ledger</h4>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.ledger', $item->id) }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                        <a href="{{ route('admin.reports.ledger', $item->id) }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
            <h5 class="mb-0">Ledger for: {{ $item->name }}</h5>
            <a href="{{ route('admin.reports.stock') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Stock Report
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-label-secondary p-3 text-center">
                        <small class="text-muted text-uppercase fw-semibold">Pack Size</small>
                        <h4 class="mb-0 mt-1">{{ $item->pack_size }} <span class="fs-6 fw-normal text-muted">units/pack</span></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-label-primary p-3 text-center">
                        <small class="text-muted text-uppercase fw-semibold">Opening Balance</small>
                        <h4 class="mb-0 mt-1">{{ number_format($openingBalance, 2) }} <span class="fs-6 fw-normal text-muted">units</span></h4>
                        <small class="text-muted mt-1">{{ $item->pack_size > 0 ? intval($openingBalance / $item->pack_size) : 0 }} packs</small>
                    </div>
                </div>
                <div class="col-md-3">
                    @php
                        $inPeriodQty = $ledgers->sum(function($l) { 
                            return $l->transaction_type == 'purchase' ? ($l->purchase_packets ?? 0) : 0; 
                        });
                        $outPeriodQty = $ledgers->sum(function($l) { 
                            return $l->transaction_type == 'sale' ? abs($l->quantity) : 0; 
                        });
                        $closingBalance = $openingBalance + $inPeriodQty - $outPeriodQty;
                    @endphp
                    <div class="card bg-label-success p-3 text-center">
                        <small class="text-muted text-uppercase fw-semibold">Period In / Out</small>
                        <h4 class="mb-0 mt-1">+{{ number_format($inPeriodQty, 2) }} / -{{ number_format($outPeriodQty, 2) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-label-info p-3 text-center">
                        <small class="text-muted text-uppercase fw-semibold">Closing Balance</small>
                        <h4 class="mb-0 mt-1">{{ number_format($closingBalance, 2) }} <span class="fs-6 fw-normal text-muted">units</span></h4>
                        <small class="text-muted mt-1">{{ $item->pack_size > 0 ? intval($closingBalance / $item->pack_size) : 0 }} packs</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive text-nowrap mt-3">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Reference / Bill No.</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $balance = $openingBalance;
                        @endphp
                        @forelse($ledgers as $ledger)
                            @php
                                $rowQty = $ledger->transaction_type == 'purchase' ? ($ledger->purchase_packets ?? 0) : $ledger->quantity;
                                $balance += $rowQty;
                                $dateStr = '';
                                if ($ledger->transaction_type == 'purchase') {
                                    $dateStr = $ledger->purchase_bill_date ? \Carbon\Carbon::parse($ledger->purchase_bill_date)->format('d M Y') : $ledger->created_at->format('d M Y');
                                } else {
                                    $dateStr = $ledger->sale_bill_date ? \Carbon\Carbon::parse($ledger->sale_bill_date)->format('d M Y') : $ledger->created_at->format('d M Y');
                                }
                            @endphp
                            <tr>
                                <td>{{ $dateStr }}</td>
                                <td>
                                    @if($ledger->transaction_type == 'purchase')
                                        <span class="badge bg-success">Purchase (In)</span>
                                    @else
                                        <span class="badge bg-danger">Sale (Out)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ledger->transaction_type == 'purchase')
                                        <a href="{{ route('admin.purchases.show', $ledger->transaction_id) }}" target="_blank">
                                            Bill No: {{ $ledger->purchase_bill_no }}
                                        </a>
                                    @else
                                        <a href="{{ route('admin.sales.show', $ledger->transaction_id) }}" target="_blank">
                                            Bill No: {{ $ledger->sale_bill_no }}
                                        </a>
                                    @endif
                                </td>
                                <td class="text-end font-monospace {{ $rowQty > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $rowQty > 0 ? '+' : '' }}{{ number_format($rowQty, 2) }}
                                </td>
                                <td class="text-end font-monospace"><strong>{{ number_format($balance, 2) }}</strong></td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No transactions found in this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
