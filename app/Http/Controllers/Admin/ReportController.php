<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ItemMaster;
use App\Models\StockLedger;

class ReportController extends Controller
{
    public function stockReport(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $search = $request->input('search');
        $brand_code = $request->input('brand_code');

        $query = ItemMaster::where('status', 1);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand_code', 'like', "%{$search}%")
                  ->orWhere('hsn', 'like', "%{$search}%");
            });
        }

        if ($brand_code) {
            $query->where('brand_code', $brand_code);
        }

        // Subqueries for purchases and sales in the period
        $purchasesInPeriodSub = \DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereNull('purchases.deleted_at');

        if ($start_date) {
            $purchasesInPeriodSub->where('purchases.bill_date', '>=', $start_date);
        }
        if ($end_date) {
            $purchasesInPeriodSub->where('purchases.bill_date', '<=', $end_date);
        }
        $purchasesInPeriodSelect = $purchasesInPeriodSub
            ->select('item_id', \DB::raw('SUM(packets) as period_purchases'))
            ->groupBy('item_id');

        $salesInPeriodSub = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.deleted_at');

        if ($start_date) {
            $salesInPeriodSub->where('sales.bill_date', '>=', $start_date);
        }
        if ($end_date) {
            $salesInPeriodSub->where('sales.bill_date', '<=', $end_date);
        }
        $salesInPeriodSelect = $salesInPeriodSub
            ->select('item_id', \DB::raw('SUM(quantity + COALESCE(free_qty, 0)) as period_sales'))
            ->groupBy('item_id');

        // Subqueries for transactions before start_date (to compute calculated opening stock)
        if ($start_date) {
            $purchasesBeforeSubSelect = \DB::table('purchase_items')
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->whereNull('purchases.deleted_at')
                ->where('purchases.bill_date', '<', $start_date)
                ->select('item_id', \DB::raw('SUM(packets) as before_purchases'))
                ->groupBy('item_id');

            $salesBeforeSubSelect = \DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.deleted_at')
                ->where('sales.bill_date', '<', $start_date)
                ->select('item_id', \DB::raw('SUM(quantity + COALESCE(free_qty, 0)) as before_sales'))
                ->groupBy('item_id');

            $query->leftJoinSub($purchasesBeforeSubSelect, 'p_before', function($join) {
                $join->on('item_masters.id', '=', 'p_before.item_id');
            })
            ->leftJoinSub($salesBeforeSubSelect, 's_before', function($join) {
                $join->on('item_masters.id', '=', 's_before.item_id');
            });
        }

        $query->leftJoinSub($purchasesInPeriodSelect, 'p_period', function($join) {
            $join->on('item_masters.id', '=', 'p_period.item_id');
        })
        ->leftJoinSub($salesInPeriodSelect, 's_period', function($join) {
            $join->on('item_masters.id', '=', 's_period.item_id');
        });

        if ($start_date) {
            $query->select(
                'item_masters.*',
                \DB::raw('COALESCE(p_period.period_purchases, 0) as purchased_qty'),
                \DB::raw('COALESCE(s_period.period_sales, 0) as sold_qty'),
                \DB::raw('(item_masters.opening_stock + COALESCE(p_before.before_purchases, 0) - COALESCE(s_before.before_sales, 0)) as calculated_opening_stock')
            );
        } else {
            $query->select(
                'item_masters.*',
                \DB::raw('COALESCE(p_period.period_purchases, 0) as purchased_qty'),
                \DB::raw('COALESCE(s_period.period_sales, 0) as sold_qty'),
                \DB::raw('item_masters.opening_stock as calculated_opening_stock')
            );
        }

        $items = $query->orderBy('name', 'asc')->get();

        $brands = ItemMaster::whereNotNull('brand_code')
            ->where('brand_code', '!=', '')
            ->distinct()
            ->orderBy('brand_code', 'asc')
            ->pluck('brand_code');

        return view('admin.reports.stock', compact('items', 'brands', 'start_date', 'end_date', 'search', 'brand_code'));
    }

    public function stockLedger(Request $request, $id)
    {
        $item = ItemMaster::findOrFail($id);
        
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = StockLedger::where('stock_ledgers.item_id', $id);

        if ($start_date) {
            // Need to calculate opening balance before start_date
            $beforePurchases = \DB::table('purchase_items')
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->whereNull('purchases.deleted_at')
                ->where('purchase_items.item_id', $id)
                ->where('purchases.bill_date', '<', $start_date)
                ->sum('purchase_items.packets');

            $beforeSales = \DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.deleted_at')
                ->where('sale_items.item_id', $id)
                ->where('sales.bill_date', '<', $start_date)
                ->sum(\DB::raw('sale_items.quantity + COALESCE(sale_items.free_qty, 0)'));

            $openingBalance = $item->opening_stock + $beforePurchases - $beforeSales;
        } else {
            $openingBalance = $item->opening_stock;
        }

        // Filter ledger entries by bill date of the transaction
        $query->select(
            'stock_ledgers.*',
            'purchases.bill_no as purchase_bill_no',
            'purchases.bill_date as purchase_bill_date',
            'purchase_items.packets as purchase_packets',
            'sales.bill_no as sale_bill_no',
            'sales.bill_date as sale_bill_date'
        )
            ->leftJoin('purchases', function($join) {
                $join->on('stock_ledgers.transaction_id', '=', 'purchases.id')
                     ->where('stock_ledgers.transaction_type', '=', 'purchase');
            })
            ->leftJoin('purchase_items', function($join) {
                $join->on('stock_ledgers.transaction_id', '=', 'purchase_items.purchase_id')
                     ->on('stock_ledgers.item_id', '=', 'purchase_items.item_id')
                     ->where('stock_ledgers.transaction_type', '=', 'purchase');
            })
            ->leftJoin('sales', function($join) {
                $join->on('stock_ledgers.transaction_id', '=', 'sales.id')
                     ->where('stock_ledgers.transaction_type', '=', 'sale');
            });

        if ($start_date) {
            $query->where(function($q) use ($start_date) {
                $q->where(function($sub) use ($start_date) {
                    $sub->where('stock_ledgers.transaction_type', 'purchase')
                        ->where('purchases.bill_date', '>=', $start_date);
                })->orWhere(function($sub) use ($start_date) {
                    $sub->where('stock_ledgers.transaction_type', 'sale')
                        ->where('sales.bill_date', '>=', $start_date);
                });
            });
        }

        if ($end_date) {
            $query->where(function($q) use ($end_date) {
                $q->where(function($sub) use ($end_date) {
                    $sub->where('stock_ledgers.transaction_type', 'purchase')
                        ->where('purchases.bill_date', '<=', $end_date);
                })->orWhere(function($sub) use ($end_date) {
                    $sub->where('stock_ledgers.transaction_type', 'sale')
                        ->where('sales.bill_date', '<=', $end_date);
                });
            });
        }

        $ledgers = $query->orderBy('stock_ledgers.id', 'asc')->get();

        return view('admin.reports.ledger', compact('item', 'ledgers', 'openingBalance', 'start_date', 'end_date'));
    }
}

