<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Models\ItemMaster;
use App\Models\StockLedger;
use DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('vendor')->orderBy('id', 'desc')->get();
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $vendors = Vendor::where('status', 1)->get();
        $items = ItemMaster::where('status', 1)->get();
        return view('admin.purchases.create', compact('vendors', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required',
            'bill_no' => 'required',
            'bill_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total_amount = 0;
            foreach ($request->items as $itemData) {
                $total_amount += ($itemData['quantity'] * $itemData['rate']);
            }

            $purchase = Purchase::create([
                'vendor_id' => $request->vendor_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => 'completed',
            ]);

            foreach ($request->items as $itemData) {
                $amount = $itemData['quantity'] * $itemData['rate'];
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ]);

                // Update Stock
                $item = ItemMaster::find($itemData['item_id']);
                $newStock = $item->current_stock + $itemData['quantity'];
                
                StockLedger::create([
                    'item_id' => $item->id,
                    'transaction_type' => 'purchase',
                    'transaction_id' => $purchase->id,
                    'quantity' => $itemData['quantity'],
                    'running_balance' => $newStock,
                ]);

                $item->update(['current_stock' => $newStock]);
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase bill saved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['vendor', 'items.item'])->findOrFail($id);
        return view('admin.purchases.show', compact('purchase'));
    }
}

