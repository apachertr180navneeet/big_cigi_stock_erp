@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Masters /</span> Item Masters</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Item Master List</h5>
            <a href="{{ route('admin.item_masters.create') }}" class="btn btn-primary">Add Item</a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>HSN</th>
                        <th>Brand Code</th>
                        <th>MRP</th>
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
                        <td>{{ $item->mrp }}</td>
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
                        <td colspan="6" class="text-center">No data found</td>
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
