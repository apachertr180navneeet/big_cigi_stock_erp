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
        $purchases = Purchase::with('vendor')->orderBy('id', 'desc')->paginate(10);
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
            $subtotal = 0;
            $total_qty = 0;

            foreach ($request->items as $itemData) {
                $qty = floatval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $line_amount = !empty($itemData['amount']) && floatval($itemData['amount']) > 0 
                    ? floatval($itemData['amount']) 
                    : round($qty * $rate, 2);

                $subtotal += $line_amount;
                $total_qty += $qty;
            }

            $discount_allowed = floatval($request->discount_allowed ?? 0);
            $net_taxable = max(0, $subtotal - $discount_allowed);
            $cgst_total = floatval($request->cgst_total ?? 0);
            $sgst_total = floatval($request->sgst_total ?? 0);

            $total_amount = floatval($request->total_amount ?? ($net_taxable + $cgst_total + $sgst_total));
            if ($total_amount <= 0) {
                $total_amount = round($net_taxable + $cgst_total + $sgst_total, 2);
            }

            $purchaseData = [
                'vendor_id' => $request->vendor_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => $request->status ?? 'completed',
            ];

            if (Schema::hasColumn('purchases', 'eway_bill_no')) {
                $purchaseData['eway_bill_no'] = $request->eway_bill_no ?? null;
                $purchaseData['supplier_invoice_no'] = $request->supplier_invoice_no ?? null;
                $purchaseData['supplier_invoice_date'] = $request->supplier_invoice_date ?? null;
                $purchaseData['other_references'] = $request->other_references ?? null;
                $purchaseData['discount_allowed'] = $discount_allowed;
                $purchaseData['cgst_total'] = $cgst_total;
                $purchaseData['sgst_total'] = $sgst_total;
            }

            $purchase = Purchase::create($purchaseData);

            foreach ($request->items as $itemData) {
                $qty = floatval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $line_amount = !empty($itemData['amount']) && floatval($itemData['amount']) > 0 
                    ? floatval($itemData['amount']) 
                    : round($qty * $rate, 2);

                $prop_disc = ($subtotal > 0 && $discount_allowed > 0) ? round(($line_amount / $subtotal) * $discount_allowed, 2) : 0;
                $item_taxable = max(0, $line_amount - $prop_disc);
                $item_cgst = ($net_taxable > 0 && $cgst_total > 0) ? round(($item_taxable / $net_taxable) * $cgst_total, 2) : 0;
                $item_sgst = ($net_taxable > 0 && $sgst_total > 0) ? round(($item_taxable / $net_taxable) * $sgst_total, 2) : 0;
                $item_tax = $item_cgst + $item_sgst;
                $final_item_amount = $item_taxable + $item_tax;
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'no_of_package' => $itemData['no_of_package'] ?? 0,
                    'uom' => $itemData['uom'] ?? 'PCS',
                    'quantity' => $qty,
                    'rate' => $rate,
                    'discount_amount' => $prop_disc,
                    'packets' => !empty($itemData['packets']) ? $itemData['packets'] : $qty,
                    'mrp' => !empty($itemData['mrp']) ? $itemData['mrp'] : $rate,
                    'taxable_value' => $item_taxable,
                    'cgst_rate' => ($item_taxable > 0 && $item_cgst > 0) ? round(($item_cgst / $item_taxable) * 100, 2) : 0,
                    'cgst_amount' => $item_cgst,
                    'sgst_rate' => ($item_taxable > 0 && $item_sgst > 0) ? round(($item_sgst / $item_taxable) * 100, 2) : 0,
                    'sgst_amount' => $item_sgst,
                    'tax_amount' => $item_tax,
                    'amount' => $final_item_amount,
                ]);

                // Update Stock
                $item = ItemMaster::find($itemData['item_id']);
                if ($item) {
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

    public function edit($id)
    {
        $purchase = Purchase::with(['vendor', 'items.item'])->findOrFail($id);
        $vendors = Vendor::where('status', 1)->get();
        $items = ItemMaster::where('status', 1)->get();
        return view('admin.purchases.edit', compact('purchase', 'vendors', 'items'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

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
            // Revert stock for existing items
            foreach ($purchase->items as $oldItem) {
                $itemMaster = ItemMaster::find($oldItem->item_id);
                if ($itemMaster) {
                    $itemMaster->decrement('current_stock', $oldItem->quantity);
                }
            }

            // Remove old stock ledgers & items
            StockLedger::where('transaction_type', 'purchase')->where('transaction_id', $purchase->id)->delete();
            $purchase->items()->delete();

            $subtotal = 0;
            $total_qty = 0;

            foreach ($request->items as $itemData) {
                $qty = floatval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $line_amount = !empty($itemData['amount']) && floatval($itemData['amount']) > 0 
                    ? floatval($itemData['amount']) 
                    : round($qty * $rate, 2);

                $subtotal += $line_amount;
                $total_qty += $qty;
            }

            $discount_allowed = floatval($request->discount_allowed ?? 0);
            $net_taxable = max(0, $subtotal - $discount_allowed);
            $cgst_total = floatval($request->cgst_total ?? 0);
            $sgst_total = floatval($request->sgst_total ?? 0);

            $total_amount = floatval($request->total_amount ?? ($net_taxable + $cgst_total + $sgst_total));
            if ($total_amount <= 0) {
                $total_amount = round($net_taxable + $cgst_total + $sgst_total, 2);
            }

            $updateData = [
                'vendor_id' => $request->vendor_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => $request->status ?? 'completed',
            ];

            if (Schema::hasColumn('purchases', 'eway_bill_no')) {
                $updateData['eway_bill_no'] = $request->eway_bill_no ?? null;
                $updateData['supplier_invoice_no'] = $request->supplier_invoice_no ?? null;
                $updateData['supplier_invoice_date'] = $request->supplier_invoice_date ?? null;
                $updateData['other_references'] = $request->other_references ?? null;
                $updateData['discount_allowed'] = $discount_allowed;
                $updateData['cgst_total'] = $cgst_total;
                $updateData['sgst_total'] = $sgst_total;
            }

            $purchase->update($updateData);

            foreach ($request->items as $itemData) {
                $qty = floatval($itemData['quantity']);
                $rate = floatval($itemData['rate']);
                $line_amount = !empty($itemData['amount']) && floatval($itemData['amount']) > 0 
                    ? floatval($itemData['amount']) 
                    : round($qty * $rate, 2);

                $prop_disc = ($subtotal > 0 && $discount_allowed > 0) ? round(($line_amount / $subtotal) * $discount_allowed, 2) : 0;
                $item_taxable = max(0, $line_amount - $prop_disc);
                $item_cgst = ($net_taxable > 0 && $cgst_total > 0) ? round(($item_taxable / $net_taxable) * $cgst_total, 2) : 0;
                $item_sgst = ($net_taxable > 0 && $sgst_total > 0) ? round(($item_taxable / $net_taxable) * $sgst_total, 2) : 0;
                $item_tax = $item_cgst + $item_sgst;
                $final_amount = $item_taxable + $item_tax;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'no_of_package' => $itemData['no_of_package'] ?? 0,
                    'uom' => $itemData['uom'] ?? 'PCS',
                    'quantity' => $qty,
                    'rate' => $rate,
                    'discount_amount' => $prop_disc,
                    'packets' => !empty($itemData['packets']) ? $itemData['packets'] : $qty,
                    'mrp' => !empty($itemData['mrp']) ? $itemData['mrp'] : $rate,
                    'taxable_value' => $item_taxable,
                    'cgst_rate' => ($item_taxable > 0 && $item_cgst > 0) ? round(($item_cgst / $item_taxable) * 100, 2) : 0,
                    'cgst_amount' => $item_cgst,
                    'sgst_rate' => ($item_taxable > 0 && $item_sgst > 0) ? round(($item_sgst / $item_taxable) * 100, 2) : 0,
                    'sgst_amount' => $item_sgst,
                    'tax_amount' => $item_tax,
                    'amount' => $final_amount,
                ]);

                // Update Stock
                $item = ItemMaster::find($itemData['item_id']);
                if ($item) {
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
            }

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase bill updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
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
            $extractedBillNo = null;
            $extractedBillDate = null;
            $extractedVendorName = null;
            $extractedGstin = null;

            // 1. Scan top header rows (first 30 rows) for Vendor, Invoice No., Date, and GSTIN
            $topRowsCount = min(30, count($rows));
            $allVendors = Vendor::all();
            $hasGstNumberCol = Schema::hasColumn('vendors', 'gst_number');
            $hasGstinCol = Schema::hasColumn('vendors', 'gstin');

            for ($rIdx = 0; $rIdx < $topRowsCount; $rIdx++) {
                $row = $rows[$rIdx];
                $rowStr = implode(' ', array_map('strval', $row));

                // A. Extract Invoice No.
                if (!$extractedBillNo) {
                    foreach ($row as $cIdx => $cellVal) {
                        $cellStr = trim((string)$cellVal);
                        if (preg_match('/invoice\s*no|bill\s*no/i', $cellStr)) {
                            // Check same cell if it has value after label
                            if (preg_match('/(?:invoice\s*no\.?|bill\s*no\.?)\s*[:\-\s]\s*([A-Z0-9\-\/]+)/i', $cellStr, $bMatch)) {
                                $extractedBillNo = trim($bMatch[1]);
                            }
                            // Check next cells in same row
                            if (!$extractedBillNo) {
                                for ($k = $cIdx + 1; $k < count($row); $k++) {
                                    $v = trim((string)($row[$k] ?? ''));
                                    if (!empty($v) && strlen($v) >= 3 && !preg_match('/^(e-way|dated|date|supplier|other)/i', $v)) {
                                        $extractedBillNo = preg_replace('/^[:\-\s]+/', '', $v);
                                        break;
                                    }
                                }
                            }
                            // Check cell directly below in next row
                            if (!$extractedBillNo && isset($rows[$rIdx + 1][$cIdx])) {
                                $vBelow = trim((string)$rows[$rIdx + 1][$cIdx]);
                                if (!empty($vBelow) && strlen($vBelow) >= 3 && !preg_match('/^(supplier|consignee|dated|date)/i', $vBelow)) {
                                    $extractedBillNo = preg_replace('/^[:\-\s]+/', '', $vBelow);
                                }
                            }
                        }
                    }
                }

                // B. Extract Invoice Date
                if (!$extractedBillDate) {
                    if (preg_match('/(?:dt\.?|dated|date)\s*[:\-\s]?\s*([0-9]{1,2}[\/\-\.][A-Za-z0-9]{2,3}[\/\-\.][0-9]{2,4})/i', $rowStr, $dMatch)) {
                        $rawDt = $dMatch[1];
                        $timestamp = strtotime($rawDt);
                        if ($timestamp) {
                            $extractedBillDate = date('Y-m-d', $timestamp);
                        }
                    }
                    if (!$extractedBillDate) {
                        foreach ($row as $cIdx => $cellVal) {
                            $cellStr = trim((string)$cellVal);
                            if (preg_match('/^(dated|date)$/i', $cellStr)) {
                                for ($k = $cIdx + 1; $k < count($row); $k++) {
                                    $v = trim((string)($row[$k] ?? ''));
                                    if (!empty($v)) {
                                        $cleanV = preg_replace('/^dt\.?\s*/i', '', $v);
                                        $timestamp = strtotime($cleanV);
                                        if ($timestamp) {
                                            $extractedBillDate = date('Y-m-d', $timestamp);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // C. Extract Supplier / Vendor & GSTIN
                if (stripos($rowStr, 'Supplier') !== false || stripos($rowStr, 'Bill from') !== false) {
                    // Look for vendor name in subsequent rows
                    for ($s = 1; $s <= 4; $s++) {
                        if (isset($rows[$rIdx + $s])) {
                            $suppRowStr = implode(' ', array_map('strval', $rows[$rIdx + $s]));
                            if (!empty(trim($suppRowStr)) && !preg_match('/(consignee|ship to|buyer|gstin)/i', $suppRowStr)) {
                                $candName = trim((string)($rows[$rIdx + $s][0] ?? $rows[$rIdx + $s][1] ?? ''));
                                if (!empty($candName) && strlen($candName) > 2) {
                                    $extractedVendorName = $candName;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (preg_match('/[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}/i', $rowStr, $gMatches)) {
                    $extractedGstin = strtoupper($gMatches[0]);
                }
            }

            // Match vendor by GSTIN first
            if ($extractedGstin) {
                if ($hasGstNumberCol) {
                    $vendor = Vendor::where('gst_number', $extractedGstin)->first();
                    if ($vendor) $matchedVendorId = $vendor->id;
                } elseif ($hasGstinCol) {
                    $vendor = Vendor::where('gstin', $extractedGstin)->first();
                    if ($vendor) $matchedVendorId = $vendor->id;
                }
            }

            // Match vendor by Name (exact or normalized)
            if (!$matchedVendorId) {
                foreach ($allVendors as $vendor) {
                    $vNameClean = preg_replace('/[^a-z0-9]/i', '', strtolower($vendor->name));
                    if (!empty($extractedVendorName)) {
                        $eNameClean = preg_replace('/[^a-z0-9]/i', '', strtolower($extractedVendorName));
                        if (!empty($vNameClean) && ($vNameClean === $eNameClean || str_contains($eNameClean, $vNameClean) || str_contains($vNameClean, $eNameClean))) {
                            $matchedVendorId = $vendor->id;
                            break;
                        }
                    }
                }
            }

            // Fallback scan top rows for existing vendor names
            if (!$matchedVendorId) {
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

            // Auto-create vendor if vendor not found but extracted
            if (!$matchedVendorId && !empty($extractedVendorName)) {
                $newVendorData = [
                    'name' => $extractedVendorName,
                    'phone' => null,
                    'address' => 'Jaipur, Rajasthan',
                    'status' => 1,
                ];
                if ($hasGstNumberCol && $extractedGstin) {
                    $newVendorData['gst_number'] = $extractedGstin;
                } elseif ($hasGstinCol && $extractedGstin) {
                    $newVendorData['gstin'] = $extractedGstin;
                }
                $newVendor = Vendor::create($newVendorData);
                $matchedVendorId = $newVendor->id;
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

            // 3. Parse table rows & summary footer rows
            $detectedSubtotal = 0;
            $detectedDiscount = 0;
            $detectedSgst = 0;
            $detectedCgst = 0;
            $detectedTotal = 0;

            for ($i = $startIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowStr = implode(' ', array_map('strval', $row));

                // Check for summary/footer rows (Less Discount, SGST, CGST, Total)
                if (preg_match('/(less:\s*discount|discount\s*allowed|sgst|cgst|igst|amount\s*chargeable|authorised\s*signatory|\btotal\b)/i', $rowStr)) {
                    // Extract global discount if present
                    if (preg_match('/(?:less:\s*)?discount(?:\s*allowed)?/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/\-?\s*([\d\.\,]+)/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[1]));
                                if ($num > 0 && $num < 10000000) {
                                    $detectedDiscount = $num;
                                    break;
                                }
                            }
                        }
                    }
                    // Extract SGST amount if present
                    if (preg_match('/\bsgst\b/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/[\d\.\,]+/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[0]));
                                if ($num > 10) $detectedSgst = $num;
                            }
                        }
                    }
                    // Extract CGST amount if present
                    if (preg_match('/\bcgst\b/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/[\d\.\,]+/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[0]));
                                if ($num > 10) $detectedCgst = $num;
                            }
                        }
                    }
                    // Extract Total if present in footer row
                    if (preg_match('/^total\b|[\s\t]total\b/i', $rowStr) && !preg_match('/subtotal/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/[₹]?\s*([\d\,]+\.\d{2})/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[1]));
                                if ($num > $detectedTotal) $detectedTotal = $num;
                            }
                        }
                    }

                    $rawDescCheck = strtolower(trim((string)($row[$colMap['desc']] ?? '')));
                    if (in_array($rawDescCheck, ['total', 'less:', 'sgst', 'cgst', 'igst', '']) || str_starts_with($rawDescCheck, 'amount chargeable') || str_contains($rawDescCheck, 'discount allowed')) {
                        continue;
                    }
                }

                $desc = trim((string)($row[$colMap['desc']] ?? ''));
                if (empty($desc) || strtolower($desc) === 'description of goods' || strtolower($desc) === 'description' || preg_match('/^(total|subtotal|less:|sgst|cgst|amount chargeable|for\s+[a-z]+)/i', $desc)) {
                    continue;
                }

                // Parse quantity & UOM
                $qtyRaw = trim((string)($row[$colMap['qty']] ?? ''));
                $qty = 0;
                $uom = '';

                if (preg_match('/([\d\.\,]+)/', $qtyRaw, $qMatches)) {
                    $qty = floatval(str_replace(',', '', $qMatches[0]));
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

                // Parse Discount %
                $discRaw = trim((string)($row[$colMap['disc']] ?? '0'));
                $discRawClean = preg_replace('/[^\d\.]/', '', $discRaw);
                $disc = floatval($discRawClean);

                // Parse Amount
                $amtRaw = trim((string)($row[$colMap['amount']] ?? '0'));
                $amtRawClean = preg_replace('/[^\d\.]/', '', $amtRaw);
                $amt = floatval($amtRawClean);
                if ($amt <= 0 && $qty > 0 && $rate > 0) {
                    $amt = round($qty * $rate, 2);
                }

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
                    'discount_percent' => $disc,
                    'amount' => $amt,
                    'packets' => $packets,
                    'mrp' => $mrp,
                ];
            }

            if (empty($parsedItems)) {
                return response()->json(['success' => false, 'message' => 'No valid item rows could be parsed from the Excel file.'], 422);
            }

            // Calculate Subtotal from parsed items
            $calcSubtotal = 0;
            foreach ($parsedItems as $pItm) {
                $calcSubtotal += $pItm['amount'];
            }
            $calcSubtotal = round($calcSubtotal, 2);

            $finalDiscount = round($detectedDiscount, 2);
            $finalSgst = round($detectedSgst, 2);
            $finalCgst = round($detectedCgst, 2);
            $calcGrandTotal = $detectedTotal > 0 ? $detectedTotal : round($calcSubtotal - $finalDiscount + $finalSgst + $finalCgst, 2);

            return response()->json([
                'success' => true,
                'vendor_id' => $matchedVendorId,
                'vendor_name' => $extractedVendorName,
                'bill_no' => $extractedBillNo,
                'bill_date' => $extractedBillDate,
                'subtotal' => $calcSubtotal,
                'discount_allowed' => $finalDiscount,
                'sgst_total' => $finalSgst,
                'cgst_total' => $finalCgst,
                'total_amount' => $calcGrandTotal,
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


