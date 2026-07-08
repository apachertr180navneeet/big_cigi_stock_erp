@extends('admin.layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Masters / Item Masters /</span> Edit</h4>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Item Master</h5>
            <a href="{{ route('admin.item_masters.index') }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="card-body">

            <form action="{{ route('admin.item_masters.update', $itemMaster->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <h6 class="mb-3">Basic Details</h6>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $itemMaster->name) }}" />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="hsn">HSN</label>
                        <input type="text" class="form-control @error('hsn') is-invalid @enderror" id="hsn" name="hsn" value="{{ old('hsn', $itemMaster->hsn) }}" />
                        @error('hsn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="brand_code">Brand Code</label>
                        <input type="text" class="form-control @error('brand_code') is-invalid @enderror" id="brand_code" name="brand_code" value="{{ old('brand_code', $itemMaster->brand_code) }}" />
                        @error('brand_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="mb-3 mt-4">Stock Details</h6>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="opening_stock">Opening Stock (Units)</label>
                        <input type="number" step="0.01" class="form-control @error('opening_stock') is-invalid @enderror" id="opening_stock" name="opening_stock" value="{{ old('opening_stock', $itemMaster->opening_stock) }}" />
                        @error('opening_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="pack_size">Pack Size (Units per Pack)</label>
                        <input type="number" min="1" class="form-control @error('pack_size') is-invalid @enderror" id="pack_size" name="pack_size" value="{{ old('pack_size', $itemMaster->pack_size) }}" />
                        @error('pack_size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="mb-3 mt-4">Other Details</h6>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="sale_price">Sale Price</label>
                        <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $itemMaster->sale_price) }}" />
                        @error('sale_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description', $itemMaster->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
