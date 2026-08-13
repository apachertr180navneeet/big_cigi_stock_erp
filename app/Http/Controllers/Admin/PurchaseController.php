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
            'items.*.no_of_package' => 'nullable|numeric|min:0',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.packets' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'items.*.cgst_rate' => 'nullable|numeric|min:0',
            'items.*.sgst_rate' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $total_amount = 0;
            $cgst_total_sum = 0;
            $sgst_total_sum = 0;
            $discount_total_sum = 0;

            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = !empty($itemData['packets']) ? $itemData['packets'] : $qty;
                $mrp = !empty($itemData['mrp']) ? $itemData['mrp'] : $rate;
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
                $cgst_total_sum += $cgst_amount;
                $sgst_total_sum += $sgst_amount;
                $discount_total_sum += $discount;
            }

            $purchaseData = [
                'vendor_id' => $request->vendor_id,
                'bill_no' => $request->bill_no,
                'bill_date' => $request->bill_date,
                'total_amount' => $total_amount,
                'status' => 'completed',
            ];

            if (Schema::hasColumn('purchases', 'eway_bill_no')) {
                $purchaseData['eway_bill_no'] = $request->eway_bill_no ?? null;
                $purchaseData['supplier_invoice_no'] = $request->supplier_invoice_no ?? null;
                $purchaseData['supplier_invoice_date'] = $request->supplier_invoice_date ?? null;
                $purchaseData['other_references'] = $request->other_references ?? null;
                $purchaseData['discount_allowed'] = $discount_total_sum;
                $purchaseData['cgst_total'] = $cgst_total_sum;
                $purchaseData['sgst_total'] = $sgst_total_sum;
            }

            $purchase = Purchase::create($purchaseData);

            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = !empty($itemData['packets']) ? $itemData['packets'] : $qty;
                $mrp = !empty($itemData['mrp']) ? $itemData['mrp'] : $rate;
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
                    'uom' => $itemData['uom'] ?? 'PCS',
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
            'items.*.no_of_package' => 'nullable|numeric|min:0',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.packets' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'nullable|numeric|min:0',
            'items.*.cgst_rate' => 'nullable|numeric|min:0',
            'items.*.sgst_rate' => 'nullable|numeric|min:0',
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

            $total_amount = 0;
            $cgst_total_sum = 0;
            $sgst_total_sum = 0;
            $discount_total_sum = 0;

            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = !empty($itemData['packets']) ? $itemData['packets'] : $qty;
                $mrp = !empty($itemData['mrp']) ? $itemData['mrp'] : $rate;
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
                $cgst_total_sum += $cgst_amount;
                $sgst_total_sum += $sgst_amount;
                $discount_total_sum += $discount;
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
                $updateData['discount_allowed'] = $discount_total_sum;
                $updateData['cgst_total'] = $cgst_total_sum;
                $updateData['sgst_total'] = $sgst_total_sum;
            }

            $purchase->update($updateData);

            foreach ($request->items as $itemData) {
                $qty = $itemData['quantity'];
                $rate = $itemData['rate'];
                $discount = $itemData['discount_amount'] ?? 0;
                $packets = !empty($itemData['packets']) ? $itemData['packets'] : $qty;
                $mrp = !empty($itemData['mrp']) ? $itemData['mrp'] : $rate;
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
                    'uom' => $itemData['uom'] ?? 'PCS',
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

            $parsedItems = [];
            $globalDiscountAmount = 0;
            $detectedSgst = null;
            $detectedCgst = null;

            // 3. Parse table rows & summary footer rows
            for ($i = $startIdx; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowStr = implode(' ', array_map('strval', $row));

                // Check for summary/footer rows (Less Discount, SGST, CGST)
                if (preg_match('/(less:\s*discount|discount\s*allowed|sgst|cgst|igst|amount\s*chargeable|authorised\s*signatory)/i', $rowStr)) {
                    // Extract global discount if present
                    if (preg_match('/(?:less:\s*)?discount(?:\s*allowed)?/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/\-?\s*([\d\.\,]+)/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[1]));
                                if ($num > 0) {
                                    $globalDiscountAmount = $num;
                                    break;
                                }
                            }
                        }
                    }
                    // Extract SGST / CGST amounts if present
                    if (preg_match('/sgst/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/[\d\.\,]+/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[0]));
                                if ($num > 10) $detectedSgst = $num;
                            }
                        }
                    }
                    if (preg_match('/cgst/i', $rowStr)) {
                        foreach ($row as $cell) {
                            $cVal = trim((string)$cell);
                            if (preg_match('/[\d\.\,]+/', $cVal, $mVal)) {
                                $num = floatval(str_replace(',', '', $mVal[0]));
                                if ($num > 10) $detectedCgst = $num;
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

            // Distribute global discount proportionally across items if present
            if ($globalDiscountAmount > 0) {
                $totalBasicSum = 0;
                foreach ($parsedItems as $pItm) {
                    $totalBasicSum += ($pItm['quantity'] * $pItm['rate']);
                }
                if ($totalBasicSum > 0) {
                    foreach ($parsedItems as &$pItm) {
                        $lineBasic = $pItm['quantity'] * $pItm['rate'];
                        $propDisc = round(($lineBasic / $totalBasicSum) * $globalDiscountAmount, 2);
                        if ($pItm['discount_amount'] <= 0) {
                            $pItm['discount_amount'] = $propDisc;
                        }
                    }
                    unset($pItm);
                }
            }

            // Calculate tax rates if tax amounts detected
            if ($detectedSgst && $detectedCgst) {
                $totalNetSum = 0;
                foreach ($parsedItems as $pItm) {
                    $totalNetSum += (($pItm['quantity'] * $pItm['rate']) - $pItm['discount_amount']);
                }
                if ($totalNetSum > 0) {
                    $calcCgstRate = round(($detectedCgst / $totalNetSum) * 100, 2);
                    $calcSgstRate = round(($detectedSgst / $totalNetSum) * 100, 2);
                    foreach ($parsedItems as &$pItm) {
                        $pItm['cgst_rate'] = $calcCgstRate;
                        $pItm['sgst_rate'] = $calcSgstRate;
                    }
                    unset($pItm);
                }
            }

            return response()->json([
                'success' => true,
                'vendor_id' => $matchedVendorId,
                'bill_no' => $extractedBillNo,
                'bill_date' => $extractedBillDate,
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


