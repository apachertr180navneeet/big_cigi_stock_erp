@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Purchases /</span> Purchase Bills</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase List</h5>
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">Add Purchase</a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th>Vendor</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->bill_no }}</td>
                        <td>{{ $purchase->bill_date }}</td>
                        <td>{{ $purchase->vendor->name ?? 'N/A' }}</td>
                        <td>{{ $purchase->total_amount }}</td>
                        <td><span class="badge bg-success">{{ $purchase->status }}</span></td>
                        <td>
                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No purchases found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
