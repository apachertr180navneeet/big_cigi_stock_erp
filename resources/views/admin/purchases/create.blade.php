@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Purchases /</span> Add Purchase</h4>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">New Purchase Bill</h5>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('admin.purchases.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bill No</label>
                        <input type="text" name="bill_no" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bill Date</label>
                        <input type="date" name="bill_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <hr>
                <h6>Items</h6>
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="items[0][item_id]" class="form-select item-select" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input" required></td>
                            <td><input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input" required></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-info mt-2 mb-3" id="addRow">Add Item</button>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let rowIdx = 1;
        $('#addRow').click(function() {
            let options = '<option value="">Select Item</option>';
            @foreach($items as $item)
                options += '<option value="{{ $item->id }}">{{ $item->name }}</option>';
            @endforeach
            let newRow = `<tr>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>
                        ${options}
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input" required></td>
                <td><input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input" required></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
            </tr>`;
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#itemsTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });

        $(document).on('change', '.item-select', function() {
        });
    });
</script>
@endsection
