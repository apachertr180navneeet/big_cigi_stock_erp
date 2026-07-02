@extends('admin.layouts.app')
@section('style')

@endsection  
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">Users</span>
    </h5>
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>View</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
$('#usersTable').DataTable({
    processing: true,
    ajax: {
      url: "{{route('admin.users.alluser')}}",
    },
    columns: [
        {
            data: "full_name",
            render: (data,type,row) => {
                let url = '{{ route("admin.users.show", ":userId") }}';
                url = url.replace(':userId', row.id);
                return '<a href="'+url+'">'+row.full_name+'</a>';
            }
        },
        {
            data: "email",
            render: (data,type,row) => {
                return '<a href="mailto:'+row.email+'">'+row.email+'</a>';
            }
        },
        {
            data: "view",
            render: (data,type,row) => {
                let url = '{{ route("admin.users.show", ":userId") }}';
                url = url.replace(':userId', row.id);
                return '<a href="'+url+'" class="btn btn-sm btn-primary">View</a>';
            }
        },
        {
            data: "status",
            render: (data,type,row) => {
                if(row.status == 'active'){
                    return '<span class="badge bg-label-success me-1">Active</span>'
                }
                if(row.status == 'inactive'){
                    return '<span class="badge bg-label-danger me-1">Inactive</span>';
                }
            }
        },
        {
            data: "action",
            render: (data,type,row) => {
                if(row.status == 'inactive'){
                    return '<button type="button" class="btn btn-sm btn-success" onclick="userStatus('+row.id+',`active`)">Active</button>';
                }
                if(row.status == 'active'){
                    return '<button type="button" class="btn btn-sm btn-danger" onclick="userStatus('+row.id+',`inactive`)">Inactive</button>';
                }
            }
        }
    ],
});



function userStatus(userid,status){
    var message = '';
    if(status == 'active'){
        message = 'User able to login after active!';
    }else{
        message = 'User cannot login after Inactive!';
    }

    
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Okey'
    }).then((result) => {
        if(result.isConfirmed == true) {
            $.ajax({
                type: "POST",
                url: "{{route('admin.users.status')}}",
                data: {'userid':userid,'status':status,'_token': "{{ csrf_token() }}"},
                success: function(response) {
                    if(response.success){
                        if(status == 1){
                            setFlesh('success','User Activate Successfully');
                        }else{
                            setFlesh('success','User Inactivate Successfully');
                        }
                        $('#usersTable').DataTable().ajax.reload();
                    }else{
                        setFlesh('error','There is some problem to change status!Please contact to your server adminstrator');
                    }
                }
            });
        }else{
            $('#usersTable').DataTable().ajax.reload();
        }
    })
}


function deleteUser(userid){
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this user!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if(result.isConfirmed == true) {
            var url = '{{ route("admin.users.destroy", ":userid") }}';
            url = url.replace(':userid', userid);
            $.ajax({
                type: "DELETE",
                url: url,
                data: {'_token': "{{ csrf_token() }}"},
                success: function(response) {
                    if(response.success){
                        setFlesh('success','User Deleted Successfully');
                        $('#usersTable').DataTable().ajax.reload();
                    }else{
                        setFlesh('error','There is some problem to delete user!Please contact to your server adminstrator');
                    }
                }
            });
        }
    })
}
</script>
@endsection
