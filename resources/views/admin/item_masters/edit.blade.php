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

                    <h6 class="mb-3 mt-4">Units & Pricing</h6>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="purchase_uom">Purchase UOM</label>
                        <input type="text" class="form-control @error('purchase_uom') is-invalid @enderror" id="purchase_uom" name="purchase_uom" value="{{ old('purchase_uom', $itemMaster->purchase_uom) }}" placeholder="e.g. M_S" />
                        @error('purchase_uom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="sales_uom">Sales UOM</label>
                        <input type="text" class="form-control @error('sales_uom') is-invalid @enderror" id="sales_uom" name="sales_uom" value="{{ old('sales_uom', $itemMaster->sales_uom) }}" placeholder="e.g. PAC" />
                        @error('sales_uom')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="conversion_factor">Conversion Factor (Sales UOM per Purchase UOM)</label>
                        <input type="number" step="any" class="form-control @error('conversion_factor') is-invalid @enderror" id="conversion_factor" name="conversion_factor" value="{{ old('conversion_factor', $itemMaster->conversion_factor) }}" />
                        @error('conversion_factor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="purchase_rate">Purchase Rate</label>
                        <input type="number" step="0.01" class="form-control @error('purchase_rate') is-invalid @enderror" id="purchase_rate" name="purchase_rate" value="{{ old('purchase_rate', $itemMaster->purchase_rate) }}" />
                        @error('purchase_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="sales_rate">Sales Rate</label>
                        <input type="number" step="0.01" class="form-control @error('sales_rate') is-invalid @enderror" id="sales_rate" name="sales_rate" value="{{ old('sales_rate', $itemMaster->sales_rate) }}" />
                        @error('sales_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="mrp">MRP</label>
                        <input type="number" step="0.01" class="form-control @error('mrp') is-invalid @enderror" id="mrp" name="mrp" value="{{ old('mrp', $itemMaster->mrp) }}" />
                        @error('mrp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="mb-3 mt-4">Taxes (%)</h6>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="cgst_percentage">CGST %</label>
                        <input type="number" step="0.01" class="form-control @error('cgst_percentage') is-invalid @enderror" id="cgst_percentage" name="cgst_percentage" value="{{ old('cgst_percentage', $itemMaster->cgst_percentage) }}" />
                        @error('cgst_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="sgst_percentage">SGST %</label>
                        <input type="number" step="0.01" class="form-control @error('sgst_percentage') is-invalid @enderror" id="sgst_percentage" name="sgst_percentage" value="{{ old('sgst_percentage', $itemMaster->sgst_percentage) }}" />
                        @error('sgst_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="mb-3 mt-4">Other Details</h6>
                    <div class="col-md-12 mb-3">
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
