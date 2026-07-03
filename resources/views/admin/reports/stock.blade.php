@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Reports /</span> Stock Report</h4>
    
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.stock') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="search">Search Item (Name/HSN/Brand)</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, HSN, brand..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="brand_code">Brand Code</label>
                        <select name="brand_code" id="brand_code" class="form-select">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" {{ request('brand_code') == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                        <a href="{{ route('admin.reports.stock') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
            <h5 class="mb-0">Stock Summary</h5>
            <span class="badge bg-label-info">
                @if(request('start_date') || request('end_date'))
                    Period: {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d M Y') : 'Beginning' }} to {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d M Y') : 'Today' }}
                @else
                    Overall Stock Summary
                @endif
            </span>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th>HSN</th>
                        <th>Brand Code</th>
                        <th>Pack Size</th>
                        <th class="text-end">Opening Stock</th>
                        <th class="text-end">Inward (Purchase)</th>
                        <th class="text-end">Outward (Sale)</th>
                        <th class="text-end">Closing Stock (Units)</th>
                        <th class="text-end">Closing Stock (Packs)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @php
                        $totalOpening = 0;
                        $totalInward = 0;
                        $totalOutward = 0;
                        $totalClosingUnits = 0;
                        $totalClosingPacks = 0;
                    @endphp
                    @forelse($items as $item)
                        @php
                            $opening = $item->calculated_opening_stock ?? 0;
                            $inward = $item->purchased_qty ?? 0;
                            $outward = $item->sold_qty ?? 0;
                            $closingUnits = $opening + $inward - $outward;
                            $closingPacks = $item->pack_size > 0 ? intval($closingUnits / $item->pack_size) : 0;

                            $totalOpening += $opening;
                            $totalInward += $inward;
                            $totalOutward += $outward;
                            $totalClosingUnits += $closingUnits;
                            $totalClosingPacks += $closingPacks;
                        @endphp
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->hsn }}</td>
                            <td>{{ $item->brand_code }}</td>
                            <td>{{ $item->pack_size }}</td>
                            <td class="text-end font-monospace">{{ number_format($opening, 2) }}</td>
                            <td class="text-end font-monospace text-success">+{{ number_format($inward, 2) }}</td>
                            <td class="text-end font-monospace text-danger">-{{ number_format($outward, 2) }}</td>
                            <td class="text-end font-monospace font-weight-bold"><strong>{{ number_format($closingUnits, 2) }}</strong></td>
                            <td class="text-end font-monospace">{{ $closingPacks }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.reports.ledger', $item->id) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn btn-sm btn-info"><i class="bx bx-list-ul me-1"></i>Ledger</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No items found matching the filter criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($items) > 0)
                <tfoot class="table-light border-top">
                    <tr>
                        <th colspan="4" class="text-end font-weight-bold">Total:</th>
                        <th class="text-end font-monospace font-weight-bold">{{ number_format($totalOpening, 2) }}</th>
                        <th class="text-end font-monospace font-weight-bold text-success">+{{ number_format($totalInward, 2) }}</th>
                        <th class="text-end font-monospace font-weight-bold text-danger">-{{ number_format($totalOutward, 2) }}</th>
                        <th class="text-end font-monospace font-weight-bold"><strong>{{ number_format($totalClosingUnits, 2) }}</strong></th>
                        <th class="text-end font-monospace font-weight-bold">{{ $totalClosingPacks }}</th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
