@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Reports /</span> Stock Ledger</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ledger for: {{ $item->name }}</h5>
            <a href="{{ route('admin.reports.stock') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <p><strong>Current Stock:</strong> {{ $item->current_stock }} {{ $item->sales_uom }}</p>
            <div class="table-responsive text-nowrap mt-3">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Quantity</th>
                            <th>Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ledgers as $ledger)
                        <tr>
                            <td>{{ $ledger->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                @if($ledger->transaction_type == 'purchase')
                                    <span class="badge bg-success">Purchase (In)</span>
                                @else
                                    <span class="badge bg-danger">Sale (Out)</span>
                                @endif
                            </td>
                            <td>{{ $ledger->quantity }}</td>
                            <td>{{ $ledger->running_balance }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
