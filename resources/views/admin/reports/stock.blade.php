@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Reports /</span> Stock Report</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Current Stock</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>HSN</th>
                        <th>Brand Code</th>
                        <th>Pack Size</th>
                        <th>Opening Stock</th>
                        <th>Current Stock (Units)</th>
                        <th>Current Stock (Packs)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->hsn }}</td>
                        <td>{{ $item->brand_code }}</td>
                        <td>{{ $item->pack_size }}</td>
                        <td>{{ $item->opening_stock }}</td>
                        <td><strong>{{ $item->current_stock }}</strong></td>
                        <td>{{ $item->pack_size > 0 ? intval($item->current_stock / $item->pack_size) : 0 }}</td>
                        <td>
                            <a href="{{ route('admin.reports.ledger', $item->id) }}" class="btn btn-sm btn-info">View Ledger</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
