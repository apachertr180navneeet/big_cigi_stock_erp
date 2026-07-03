<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ItemMaster;
use Illuminate\Http\Request;

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
            'purchase_uom' => 'nullable|string|max:255',
            'sales_uom' => 'nullable|string|max:255',
            'conversion_factor' => 'nullable|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sales_rate' => 'nullable|numeric|min:0',
            'cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'sgst_percentage' => 'nullable|numeric|min:0|max:100',
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
            'purchase_uom' => 'nullable|string|max:255',
            'sales_uom' => 'nullable|string|max:255',
            'conversion_factor' => 'nullable|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sales_rate' => 'nullable|numeric|min:0',
            'cgst_percentage' => 'nullable|numeric|min:0|max:100',
            'sgst_percentage' => 'nullable|numeric|min:0|max:100',
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
}

