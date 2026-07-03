@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sales /</span> Sale Bills</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sale List</h5>
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">Add Sale</a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bill No</th>
                        <th>Bill Date</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->bill_no }}</td>
                        <td>{{ $sale->bill_date }}</td>
                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td>{{ $sale->total_amount }}</td>
                        <td><span class="badge bg-success">{{ $sale->status }}</span></td>
                        <td>
                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No sales found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
