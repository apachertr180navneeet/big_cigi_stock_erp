<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ItemMaster;
use App\Models\StockLedger;
use DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('customer')->orderBy('id', 'desc')->get();
        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::where('status', 1)->get();
        $items = ItemMaster::where('status', 1)->get();
        return view('admin.sales.create', compact('customers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
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

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => 'completed',
            ]);

            foreach ($request->items as $itemData) {
                $amount = $itemData['quantity'] * $itemData['rate'];
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $amount,
                ]);

                // Update Stock (Decrease)
                $item = ItemMaster::find($itemData['item_id']);
                $newStock = $item->current_stock - $itemData['quantity'];
                
                StockLedger::create([
                    'item_id' => $item->id,
                    'transaction_type' => 'sale',
                    'transaction_id' => $sale->id,
                    'quantity' => -$itemData['quantity'],
                    'running_balance' => $newStock,
                ]);

                $item->update(['current_stock' => $newStock]);
            }

            DB::commit();
            return redirect()->route('admin.sales.index')->with('success', 'Sale bill saved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['customer', 'items.item'])->findOrFail($id);
        return view('admin.sales.show', compact('sale'));
    }
}
