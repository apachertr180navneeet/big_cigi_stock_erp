@extends('admin.layouts.app')
@section('style')

@endsection  
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">Notifications</span>
    </h5>
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card profile-card">
                <div class="card-body  pb-5">
                    @if(count($notifications) > 0)
                        <table class="table table-bordered table-centered mb-0" id="specificationTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>View</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($notifications as $notification)
                                    <tr>
                                        <td>
                                            <a href="{{ $notification->url }}" target="_blank">{{ $notification->title }}</a>
                                        </td>
                                        <td>{{ $notification->notification_type }}</td>
                                        <td>{{ $notification->created_at->format('d/m/Y') }}</td>
                                        <td><a href="{{ $notification->url }}" class="btn btn-primary btn-sm">View</a></td>
                                        <td><a onclick="deleteNotificatoin('{{$notification->uuid}}',this)" class="btn btn-danger btn-sm">Delete</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{$notifications->links("pagination::bootstrap-4")}}
                    @else
                        No notifcation found
                    @endif    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    function deleteNotificatoin(id,e){
        let url = '{{ route("admin.notifications.destroy", ":id") }}';
        url = url.replace(':id', id);   

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this notification.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if(result.isConfirmed == true) {
                $.ajax({
                    type: "DELETE",
                    url: url,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(response) {
                        if(response.status == true){
                            $(e).closest("tr").remove();
                            setFlesh('success','Nofication deleted successfully');
                        }
                        else{
                            setFlesh('error','Something went wrong please try again');
                        }
                    },
                    error: function(data) {
                       setFlesh('error','Something went wrong please try again');
                    }
                });
                
            }
        })
    }
</script>
@endsection
