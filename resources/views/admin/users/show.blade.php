@extends('admin.layouts.app')
@section('content')
<style>
    .thumb-image{
        height: 50px;
        width: 50px;
        border: 1px solid lightgray;
        padding: 1px;
    }
    .header-title{
        text-transform: capitalize;
        font-size:;
    }
</style>
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">User Detail</span>
    </h5>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="header-title">
                                <span>
                                @if(!empty($user->avatar) && file_exists(public_path('/').$user->avatar))
                                    <img src="{{asset($user->avatar)}}" alt="User Image" class="thumb-image rounded-circle">
                                @else
                                    <img src="{{asset("assets/admin/img/avatars/no-user.jpg")}}"  alt="User Image" class="thumb-image rounded-circle">
                                @endif
                                </span>
                                <span class="text-primary">{{$user->first_name}} {{$user->last_name}}</span>
                            </h4>
                            <hr>
                            <div class="text-left mb-3">
                                <p class="text-muted"><strong>Email Address :</strong> <span class="ml-2">{{$user->email}}</span></p>
                                @if($user->phone)
                                <p class="text-muted"><strong>Phone Number :</strong> <span class="ml-2">{{$user->phone}}</span></p>
                                @endif
                                <p class="text-muted"><strong>City:</strong> <span class="ml-2">{{$user->city}}</span></p>
                                <p class="text-muted"><strong>State:</strong> <span class="ml-2">{{$user->state}}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('script')

@endsection