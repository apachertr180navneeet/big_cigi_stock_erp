@extends('admin.layouts.app')
@section('style')
<style>
    .ck-editor__editable_inline {
        min-height: 400px;
        max-height: 500px;
    }
</style>
@endsection  
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">Pages</span>
    </h5>
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{$page->name}}</h5>
                    <form method="POST" action="{{route('admin.page.update',$page->key)}}" data-id="{{$page->id}}" enctype="multipart/form-data" id="updatePage{{$page->id}}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea name="value" id="description" class="form-control" placeholder="Enter Content" rows="8">{{$page->value}}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>                
                </div>
            </div>
        </div>
       
    </div>
</div>
@endsection
@section('script')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#description'), {
            removePlugins: ['CKFinderUploadAdapter', 'CKFinder', 'EasyImage', 'Image', 'ImageCaption', 'ImageStyle', 'ImageToolbar', 'ImageUpload', 'MediaEmbed'],
        })
</script>

<script>
$("form").submit(function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var formData = new FormData($('#updatePage'+id)[0]);
    var url = $(this).attr('action');
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response){
            if(response.success){
                setFlesh('success','Page Saved Successfully');
            }else if(response.error){
                setFlesh('error',response.message);
            }
            else{
                setFlesh('error','There is some problem to create or update page!Please contact to your server adminstrator');
            }
        }
    });       
});
</script>
@endsection



