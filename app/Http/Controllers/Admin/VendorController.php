<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Exports\VendorTemplateExport;
use App\Imports\VendorImport;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->paginate(10);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string',
        ]);

        Vendor::create($request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:10',
            'address' => 'nullable|string',
        ]);

        $vendor->update($request->all());

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return response()->json(['success' => true, 'message' => 'Vendor deleted successfully.']);
    }

    public function changeStatus(Request $request)
    {
        $vendor = Vendor::find($request->id);
        if ($vendor) {
            $vendor->status = !$vendor->status;
            $vendor->save();
            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $vendor->status]);
        }
        return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new VendorImport, $request->file('file'));
            return redirect()->route('admin.vendors.index')->with('success', 'Vendors imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.vendors.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new VendorTemplateExport, 'vendor_template.xlsx');
    }
}

