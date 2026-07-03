<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemMaster;
use App\Models\StockLedger;

class ReportController extends Controller
{
    public function stockReport()
    {
        $items = ItemMaster::where('status', 1)->get();
        return view('admin.reports.stock', compact('items'));
    }

    public function stockLedger($id)
    {
        $item = ItemMaster::findOrFail($id);
        $ledgers = StockLedger::where('item_id', $id)->orderBy('id', 'asc')->get();
        return view('admin.reports.ledger', compact('item', 'ledgers'));
    }
}
