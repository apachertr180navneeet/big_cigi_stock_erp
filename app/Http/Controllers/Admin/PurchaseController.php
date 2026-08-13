<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Models\ItemMaster;
use App\Models\StockLedger;
use App\Exports\PurchaseTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
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

    public function parseExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $sheets = Excel::toArray([], $request->file('file'));
            if (empty($sheets) || empty($sheets[0])) {
                return response()->json(['success' => false, 'message' => 'The uploaded file is empty.'], 422);
            }

            $rows = $sheets[0];
            $matchedVendorId = null;

            // 1. Scan rows for Vendor / Supplier info
            $allVendors = Vendor::all();
            $hasGstNumberCol = Schema::hasColumn('vendors', 'gst_number');
            $hasGstinCol = Schema::hasColumn('vendors', 'gstin');

            foreach ($rows as $row) {
                $rowStr = implode(' ', array_map('strval', $row));
                if (stripos($rowStr, 'Supplier') !== false || stripos($rowStr, 'Bill from') !== false || stripos($rowStr, 'GSTIN') !== false) {
                    // Try matching GSTIN pattern if column exists
                    if (preg_match('/[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}/i', $rowStr, $matches)) {
                        $gstin = strtoupper($matches[0]);
                        if ($hasGstNumberCol) {
                            $vendor = Vendor::where('gst_number', $gstin)->first();
                            if ($vendor) {
                                $matchedVendorId = $vendor->id;
                                break;
                            }
                        } elseif ($hasGstinCol) {
                            $vendor = Vendor::where('gstin', $gstin)->first();
                            if ($vendor) {
                                $matchedVendorId = $vendor->id;
                                break;
                            }
                        }
                    }
                    // Try matching Vendor Name in row text
                    foreach ($allVendors as $vendor) {
                        if (!empty($vendor->name) && stripos($rowStr, $vendor->name) !== false) {
                            $matchedVendorId = $vendor->id;
                            break 2;
                        }
                    }
                }
            }

            // Fallback: scan top 20 rows for Vendor name match
            if (!$matchedVendorId) {
                $topRowsCount = min(20, count($rows));
                for ($rIdx = 0; $rIdx < $topRowsCount; $rIdx++) {
                    $rowStr = implode(' ', array_map('strval', $rows[$rIdx]));
                    foreach ($allVendors as $vendor) {
                        if (!empty($vendor->name) && stripos($rowStr, $vendor->name) !== false) {
                            $matchedVendorId = $vendor->id;
                            break 2;
                        }
                    }
                }
            }

            // 2. Find table header row
            $headerRowIdx = null;
            $colMap = [
                'desc' => -1,
                'qty' => -1,
                'rate' => -1,
                'uom' => -1,
                'disc' => -1,
                'amount' => -1,
                'hsn' => -1,
                'brand' => -1,
            ];

            foreach ($rows as $rIdx => $row) {
                foreach ($row as $cIdx => $cellVal) {
                    $val = strtolower(trim((string)$cellVal));
                    if (str_contains($val, 'description') || str_contains($val, 'particulars') || $val === 'item name') {
                        $headerRowIdx = $rIdx;
                        break 2;
                    }
                }
            }

            if ($headerRowIdx !== null) {
                $headerRow = $rows[$headerRowIdx];
                foreach ($headerRow as $cIdx => $cellVal) {
                    $val = strtolower(trim((string)$cellVal));
                    if (str_contains($val, 'description') || str_contains($val, 'particulars') || $val === 'item name') {
                        $colMap['desc'] = $cIdx;
                    } elseif (str_contains($val, 'quantity') || $val === 'qty') {
                        $colMap['qty'] = $cIdx;
                    } elseif (str_contains($val, 'rate') || str_contains($val, 'price')) {
                        $colMap['rate'] = $cIdx;
                    } elseif ($val === 'per' || str_contains($val, 'uom') || str_contains($val, 'unit')) {
                        $colMap['uom'] = $cIdx;
                    } elseif (str_contains($val, 'disc') || str_contains($val, 'discount')) {
                        $colMap['disc'] = $cIdx;
                    } elseif ($val === 'amount' || str_contains($val, 'net amount') || str_contains($val, 'total')) {
                        $colMap['amount'] = $cIdx;
                    } elseif (str_contains($val, 'hsn')) {
                        $colMap['hsn'] = $cIdx;
                    } elseif (str_contains($val, 'brand')) {
                        $colMap['brand'] = $cIdx;
                    }
                }
            }

            // Fallbacks if specific headers were not found
            $startIdx = ($headerRowIdx !== null) ? $headerRowIdx + 1 : 0;
            if ($colMap['desc'] === -1) $colMap['desc'] = 1;
            if ($colMap['qty'] === -1) $colMap['qty'] = 2;
            if ($colMap['rate'] === -1) $colMap['rate'] = 3;
            if ($colMap['uom'] === -1) $colMap['uom'] = 4;
            if ($colMap['disc'] === -1) $colMap['disc'] = 5;
            if ($colMap['amount'] === -1) $colMap['amount'] = 6;

            $parsedItems = [];

            // 3. Parse table rows
            for ($i = $startIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowStr = implode(' ', array_map('strval', $row));

                // Stop conditions for summary/footer rows
                if (preg_match('/(total|subtotal|less:|sgst|cgst|igst|amount chargeable|e\. & o\.e|authorised signatory)/i', $rowStr) && !preg_match('/^\d+$/', trim((string)($row[0] ?? '')))) {
                    // If description contains actual product name, keep parsing, otherwise stop
                    $rawDescCheck = strtolower(trim((string)($row[$colMap['desc']] ?? '')));
                    if (in_array($rawDescCheck, ['total', 'less:', 'sgst', 'cgst', 'igst', '']) || str_starts_with($rawDescCheck, 'amount chargeable')) {
                        break;
                    }
                }

                $desc = trim((string)($row[$colMap['desc']] ?? ''));
                if (empty($desc) || strtolower($desc) === 'description of goods' || strtolower($desc) === 'description') {
                    continue;
                }

                // Parse quantity & UOM
                $qtyRaw = trim((string)($row[$colMap['qty']] ?? ''));
                $qty = 0;
                $uom = '';

                if (preg_match('/([\d\.]+)/', $qtyRaw, $qMatches)) {
                    $qty = floatval($qMatches[0]);
                }
                if (preg_match('/([a-zA-Z]+)/', $qtyRaw, $uMatches)) {
                    $uom = strtoupper($uMatches[0]);
                }

                if ($colMap['uom'] !== -1 && !empty($row[$colMap['uom']])) {
                    $uomVal = trim((string)$row[$colMap['uom']]);
                    if (!empty($uomVal) && strtolower($uomVal) !== 'per') {
                        $uom = strtoupper($uomVal);
                    }
                }
                if (empty($uom)) {
                    $uom = 'PCS';
                }

                // Parse Rate
                $rateRaw = trim((string)($row[$colMap['rate']] ?? '0'));
                $rateRawClean = preg_replace('/[^\d\.]/', '', $rateRaw);
                $rate = floatval($rateRawClean);

                // Parse Discount
                $discRaw = trim((string)($row[$colMap['disc']] ?? '0'));
                $discRawClean = preg_replace('/[^\d\.]/', '', $discRaw);
                $disc = floatval($discRawClean);

                if ($qty <= 0 && $rate <= 0) {
                    continue;
                }

                // Match or auto-create item in ItemMaster
                $item = ItemMaster::where('name', $desc)->first();
                if (!$item && $colMap['brand'] !== -1 && !empty($row[$colMap['brand']])) {
                    $brandCode = trim((string)$row[$colMap['brand']]);
                    $item = ItemMaster::where('brand_code', $brandCode)->first();
                }
                if (!$item) {
                    $item = ItemMaster::where('name', 'LIKE', '%' . $desc . '%')->first();
                }
                if (!$item) {
                    $hsnVal = ($colMap['hsn'] !== -1 && !empty($row[$colMap['hsn']])) ? trim((string)$row[$colMap['hsn']]) : null;
                    $brandVal = ($colMap['brand'] !== -1 && !empty($row[$colMap['brand']])) ? trim((string)$row[$colMap['brand']]) : null;

                    $item = ItemMaster::create([
                        'name' => $desc,
                        'description' => $desc,
                        'hsn' => $hsnVal,
                        'brand_code' => $brandVal,
                        'sale_price' => $rate > 0 ? $rate : 0,
                        'pack_size' => 1,
                        'opening_stock' => 0,
                        'current_stock' => 0,
                        'status' => 1,
                    ]);
                }

                $noOfPkg = ($item->pack_size && $item->pack_size > 0) ? round($qty / $item->pack_size, 2) : $qty;
                $packets = $qty;
                $mrp = ($item->sale_price && $item->sale_price > 0) ? $item->sale_price : ($rate > 0 ? $rate : 0);

                $parsedItems[] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'hsn' => $item->hsn ?? '',
                    'brand_code' => $item->brand_code ?? '',
                    'no_of_package' => $noOfPkg,
                    'uom' => $uom,
                    'quantity' => $qty,
                    'rate' => $rate,
                    'discount_amount' => $disc,
                    'packets' => $packets,
                    'mrp' => $mrp,
                    'cgst_rate' => 20.00,
                    'sgst_rate' => 20.00,
                ];
            }

            if (empty($parsedItems)) {
                return response()->json(['success' => false, 'message' => 'No valid item rows could be parsed from the Excel file.'], 422);
            }

            return response()->json([
                'success' => true,
                'vendor_id' => $matchedVendorId,
                'items' => $parsedItems,
                'message' => count($parsedItems) . ' item(s) successfully imported from Excel file.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Excel Import Error: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new PurchaseTemplateExport, 'purchase_import_template.xlsx');
    }
}


