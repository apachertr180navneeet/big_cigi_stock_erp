@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sales /</span> View Bill</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Sale Bill: {{ $sale->bill_no }}</h5>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Customer:</strong> {{ $sale->customer->name ?? 'N/A' }}<br>
                    <strong>Date:</strong> {{ $sale->bill_date }}
                </div>
                <div class="col-md-6 text-end">
                    <strong>Status:</strong> <span class="badge bg-success">{{ $sale->status }}</span><br>
                    <strong>Total Amount:</strong> Rs {{ number_format($sale->total_amount, 2) }}
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->item->name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->rate, 2) }}</td>
                            <td>{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th>{{ number_format($sale->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
