<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
            'items.*.no_of_package' => 'nullable|numeric|min:0',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'cess_amount' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'tcs_amount' => 'nullable|numeric|min:0',
            'credit_adj' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total_amount = 0;

            // First pass: calculate total
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount_percent = $itemData['discount_percent'] ?? 0;
                $discount_amount = $itemData['discount_amount'] ?? 0;
                $other_discount = $itemData['other_discount'] ?? 0;
                $packets = $itemData['packets'] ?? 0;
                $mrp = $itemData['mrp'] ?? 0;
                $cgst_rate = $itemData['cgst_rate'] ?? 0;
                $sgst_rate = $itemData['sgst_rate'] ?? 0;

                $basic_value = round($qty * $rate, 2);

                // If discount_percent is given and discount_amount is 0, calculate from percent
                if ($discount_percent > 0 && $discount_amount == 0) {
                    $discount_amount = round($basic_value * ($discount_percent / 100), 2);
                }

                $net_amount = round($basic_value - $discount_amount - $other_discount, 2);
                
                $total_value = round($packets * $mrp, 2);
                $taxable_value = $total_value > 0 ? round($total_value / 1.40, 2) : 0;
                $cgst_amount = round($taxable_value * ($cgst_rate / 100), 2);
                $sgst_amount = round($taxable_value * ($sgst_rate / 100), 2);
                $tax_amount = $cgst_amount + $sgst_amount;
                
                // If net amount is the only thing we have, just use it
                $item_amount = round($net_amount + $tax_amount, 2);

                $total_amount += $item_amount;
            }

            $discount_amount = $request->discount_amount ?? 0;
            $cess_amount = $request->cess_amount ?? 0;
            $round_off = $request->round_off ?? 0;
            $tcs_amount = $request->tcs_amount ?? 0;
            $credit_adj = $request->credit_adj ?? 0;
            $net_payable = round($total_amount - $discount_amount + $cess_amount + $round_off + $tcs_amount - $credit_adj, 2);

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'round_off' => $round_off,
                'tcs_amount' => $tcs_amount,
                'credit_adj' => $credit_adj,
                'net_payable' => $net_payable,
                'status' => 'completed',
            ]);

            // Second pass: create items and update stock
            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $free_qty = $itemData['free_qty'] ?? 0;
                $rate = $itemData['rate'];
                $discount_percent = $itemData['discount_percent'] ?? 0;
                $discount_amount = $itemData['discount_amount'] ?? 0;
                $other_discount = $itemData['other_discount'] ?? 0;
                $packets = $itemData['packets'] ?? 0;
                $mrp = $itemData['mrp'] ?? 0;
                $cgst_rate = $itemData['cgst_rate'] ?? 0;
                $sgst_rate = $itemData['sgst_rate'] ?? 0;

                $basic_value = round($qty * $rate, 2);

                if ($discount_percent > 0 && $discount_amount == 0) {
                    $discount_amount = round($basic_value * ($discount_percent / 100), 2);
                }

                $net_amount = round($basic_value - $discount_amount - $other_discount, 2);
                $total_value = round($packets * $mrp, 2);
                $taxable_value = $total_value > 0 ? round($total_value / 1.40, 2) : 0;
                $cgst_amount = round($taxable_value * ($cgst_rate / 100), 2);
                $sgst_amount = round($taxable_value * ($sgst_rate / 100), 2);
                $tax_amount = $cgst_amount + $sgst_amount;
                $amount = round($net_amount + $tax_amount, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_id' => $itemData['item_id'],
                    'no_of_package' => $itemData['no_of_package'] ?? 0,
                    'uom' => $itemData['uom'] ?? null,
                    'quantity' => $qty,
                    'free_qty' => $free_qty,
                    'rate' => $rate,
                    'discount_percent' => $discount_percent,
                    'discount_amount' => $discount_amount,
                    'other_discount' => $other_discount,
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

                // Update Stock (Decrease) — total dispatched = qty + free_qty
                $item = ItemMaster::find($itemData['item_id']);
                $totalDispatched = $qty + $free_qty;
                $newStock = $item->current_stock - $totalDispatched;

                StockLedger::create([
                    'item_id' => $item->id,
                    'transaction_type' => 'sale',
                    'transaction_id' => $sale->id,
                    'quantity' => -$totalDispatched,
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

    public function invoice($id)
    {
        $sale = Sale::with(['customer', 'items.item'])->findOrFail($id);
        return view('admin.sales.invoice', compact('sale'));
    }
}

