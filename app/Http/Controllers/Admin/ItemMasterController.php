<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Exports\ItemMasterTemplateExport;
use App\Imports\ItemMasterImport;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemMasterController extends Controller
{
    public function index()
    {
        $items = ItemMaster::latest()->paginate(10);
        return view('admin.item_masters.index', compact('items'));
    }

    public function create()
    {
        return view('admin.item_masters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hsn' => 'nullable|string|max:255',
            'brand_code' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'pack_size' => 'nullable|integer|min:1',
        ]);

        ItemMaster::create($request->all());

        return redirect()->route('admin.item_masters.index')->with('success', 'Item created successfully.');
    }

    public function edit(ItemMaster $itemMaster)
    {
        return view('admin.item_masters.edit', compact('itemMaster'));
    }

    public function update(Request $request, ItemMaster $itemMaster)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hsn' => 'nullable|string|max:255',
            'brand_code' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'pack_size' => 'nullable|integer|min:1',
        ]);

        $itemMaster->update($request->all());

        return redirect()->route('admin.item_masters.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(ItemMaster $itemMaster)
    {
        $itemMaster->delete();
        return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
    }

    public function changeStatus(Request $request)
    {
        $itemMaster = ItemMaster::find($request->id);
        if ($itemMaster) {
            $itemMaster->status = !$itemMaster->status;
            $itemMaster->save();
            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $itemMaster->status]);
        }
        return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new ItemMasterImport, $request->file('file'));
            return redirect()->route('admin.item_masters.index')->with('success', 'Items imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.item_masters.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ItemMasterTemplateExport, 'item_master_template.xlsx');
    }
}

