@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Masters /</span> Vendors</h4>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Vendor List</h5>
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">Add Vendor</a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($vendors as $vendor)
                    <tr id="row-{{ $vendor->id }}">
                        <td>{{ $vendor->name }}</td>
                        <td>{{ $vendor->phone }}</td>
                        <td>{{ $vendor->address }}</td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status-toggle" type="checkbox" id="status-{{ $vendor->id }}" data-id="{{ $vendor->id }}" {{ $vendor->status ? 'checked' : '' }}>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" class="btn btn-sm btn-info">Edit</a>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $vendor->id }}">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $vendors->links() }}
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
                        url: "{{ route('admin.vendors.change-status') }}",
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
                text: "You want to delete this vendor?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("admin/vendors") }}/' + id,
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
