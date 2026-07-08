@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Masters /</span> Item Masters</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Item Master List</h5>
            <div>
                <a href="{{ route('admin.item_masters.template') }}" class="btn btn-secondary">Download Template</a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">Import Excel</button>
                <a href="{{ route('admin.item_masters.create') }}" class="btn btn-primary">Add Item</a>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>HSN</th>
                        <th>Brand Code</th>
                        <th>Sale Price</th>
                        <th>Pack Size</th>
                        <th>Stock (Units)</th>
                        <th>Stock (Packs)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($items as $item)
                    <tr id="row-{{ $item->id }}">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->hsn }}</td>
                        <td>{{ $item->brand_code }}</td>
                        <td>{{ $item->sale_price }}</td>
                        <td>{{ $item->pack_size }}</td>
                        <td>{{ $item->current_stock }}</td>
                        <td>{{ intval($item->current_stock / $item->pack_size) }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox" id="status-{{ $item->id }}" data-id="{{ $item->id }}" {{ $item->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.item_masters.edit', $item->id) }}" class="btn btn-sm btn-info">Edit</a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $item->id }}">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.item_masters.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Import Items from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="file" class="form-label">Choose Excel File</label>
                    <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                <p class="text-muted small">Accepted formats: .xlsx, .xls, .csv. The file must have headers: Name, Description, HSN, Brand Code, Current Stock, Status.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.status-toggle').change(function() {
            let checkbox = $(this);
            let id = checkbox.data('id');
            let isChecked = checkbox.prop('checked');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to change the status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.item_masters.change-status') }}",
                        type: 'POST',
                        data: { id: id },
                        success: function(response) {
                            if(response.success) {
                                setFlesh('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            setFlesh('error', 'An error occurred.');
                            checkbox.prop('checked', !isChecked);
                        }
                    });
                } else {
                    checkbox.prop('checked', !isChecked);
                }
            });
        });

        $('.delete-btn').click(function() {
            let id = $(this).data('id');
            let tr = $('#row-' + id);
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this item?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("admin/item_masters") }}/' + id,
                        type: 'DELETE',
                        success: function(response) {
                            if(response.success) {
                                tr.fadeOut(400, function() {
                                    $(this).remove();
                                });
                                setFlesh('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            setFlesh('error', 'An error occurred.');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
