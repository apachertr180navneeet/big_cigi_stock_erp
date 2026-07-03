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
            'items.*.no_of_package' => 'nullable|numeric|min:0',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.packets' => 'required|numeric|min:0',
            'items.*.mrp' => 'required|numeric|min:0',
            'items.*.cgst_rate' => 'nullable|numeric|min:0',
            'items.*.sgst_rate' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total_amount = 0;
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = $itemData['packets'];
                $mrp = $itemData['mrp'];
                $cgst_rate = $itemData['cgst_rate'] ?? 20.00;
                $sgst_rate = $itemData['sgst_rate'] ?? 20.00;

                $basic_value = round($qty * $rate, 2);
                $net_amount = round($basic_value - $discount, 2);
                $total_value = round($packets * $mrp, 2);
                $taxable_value = round($total_value / 1.40, 2);
                $cgst_amount = round($taxable_value * ($cgst_rate / 100), 2);
                $sgst_amount = round($taxable_value * ($sgst_rate / 100), 2);
                $tax_amount = $cgst_amount + $sgst_amount;
                $item_amount = $net_amount + $tax_amount;

                $total_amount += $item_amount;
            }

            $purchase = Purchase::create([
                'vendor_id' => $request->vendor_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => 'completed',
            ]);

            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = $itemData['packets'];
                $mrp = $itemData['mrp'];
                $cgst_rate = $itemData['cgst_rate'] ?? 20.00;
                $sgst_rate = $itemData['sgst_rate'] ?? 20.00;

                $basic_value = round($qty * $rate, 2);
                $net_amount = round($basic_value - $discount, 2);
                $total_value = round($packets * $mrp, 2);
                $taxable_value = round($total_value / 1.40, 2);
                $cgst_amount = round($taxable_value * ($cgst_rate / 100), 2);
                $sgst_amount = round($taxable_value * ($sgst_rate / 100), 2);
                $tax_amount = $cgst_amount + $sgst_amount;
                $amount = $net_amount + $tax_amount;
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'no_of_package' => $itemData['no_of_package'] ?? 0,
                    'uom' => $itemData['uom'] ?? null,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'discount_amount' => $discount,
                    'packets' => $packets,
                    'mrp' => $mrp,
                    'taxable_value' => $taxable_value,
                    'cgst_rate' => $cgst_rate,
                    'cgst_amount' => $cgst_amount,
                    'sgst_rate' => $sgst_rate,
                    'sgst_amount' => $sgst_amount,
                    'tax_amount' => $tax_amount,
                    'amount' => $amount,
                ]);

                // Update Stock
                $item = ItemMaster::find($itemData['item_id']);
                $newStock = $item->current_stock + $qty;
                
                StockLedger::create([
                    'item_id' => $item->id,
                    'transaction_type' => 'purchase',
                    'transaction_id' => $purchase->id,
                    'quantity' => $qty,
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

