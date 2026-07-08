<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Stock Sheet - {{ $sale->bill_no }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000080;
            padding: 5px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000080;
            padding-bottom: 5px;
            margin-bottom: 5px;
            position: relative;
        }
        .gstin {
            position: absolute;
            left: 5px;
            top: 5px;
            font-weight: bold;
            font-size: 13px;
        }
        .phones {
            position: absolute;
            right: 5px;
            top: 5px;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }
        .phones img {
            width: 12px;
            vertical-align: middle;
        }
        .title {
            color: #000080;
            text-decoration: underline;
            font-weight: bold;
            font-size: 14px;
            margin: 5px 0;
        }
        .company-name {
            color: #000080;
            font-size: 28px;
            font-weight: bold;
            margin: 5px 0;
            letter-spacing: 1px;
        }
        .address {
            color: #000080;
            font-size: 13px;
            font-weight: bold;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000080;
            padding: 5px 0;
            font-weight: bold;
            font-size: 13px;
        }
        .details-row div {
            flex: 1;
            text-align: left;
            padding: 0 5px;
            border-right: 1px solid #000080;
        }
        .details-row div:last-child {
            border-right: none;
        }
        .details-row span.val {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            color: #d32f2f;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }
        
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000080;
            margin-bottom: 0;
        }
        table.main-table th, table.main-table td {
            border: 1px solid #000080;
            padding: 2px 4px;
            vertical-align: middle;
        }
        table.main-table th {
            color: #000080;
            font-size: 12px;
            text-align: center;
        }
        table.main-table td {
            height: 18px; /* Fixed height for rows */
        }
        table.main-table td.col-sno { width: 4%; text-align: center; }
        table.main-table td.col-brand { width: 30%; font-weight: normal; }
        table.main-table td.col-opening { width: 10%; text-align: center; font-weight: bold; font-family: 'Comic Sans MS', cursive, sans-serif; }
        table.main-table td.col-issue { width: 10%; }
        table.main-table td.col-receive { width: 10%; }
        table.main-table td.col-balance { width: 10%; }
        table.main-table td.col-rate { width: 10%; text-align: right; }
        table.main-table td.col-amount { width: 16%; text-align: right; }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            border: 2px solid #000080;
            border-top: none;
        }
        .footer-left {
            border-right: 2px solid #000080;
        }
        .footer-center {
            border-right: 2px solid #000080;
        }
        .footer-right {
        }
        
        table.sub-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.sub-table th, table.sub-table td {
            border: 1px solid #000080;
            padding: 3px 5px;
            font-size: 11px;
            color: #000080;
            font-weight: bold;
        }
        table.sub-table td.val-cell {
            color: #000;
            font-weight: normal;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-blue { color: #000080; }
        
        .cash-table {
            width: 100%;
            border-collapse: collapse;
            height: 100%;
        }
        .cash-table td {
            border: 1px solid #000080;
            padding: 2px 5px;
            color: #000080;
            font-weight: bold;
            font-size: 12px;
        }
        .cash-table td:first-child {
            text-align: right;
            width: 50%;
        }
        .cash-table td:last-child {
            width: 50%;
        }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-container">
    <div class="header">
        <div class="gstin">GSTIN : 08DSPPP4360G1ZA</div>
        <div class="title">DAILY STOCK SHEET</div>
        <div class="phones">
            &#9742; 9829024121<br>
            &#9742; 9799006247
        </div>
        <div class="company-name">SHREE SATYAIN INTERNATIONAL</div>
        <div class="address">Plot No. 29-B, Mandore Road, Khetanadi, Jodhpur Rajasthan (342007)</div>
    </div>
    
    <div class="details-row">
        <div>D.S. Detail Sheet</div>
        <div>D.S. Name <span class="val">{{ $sale->customer->name ?? '' }}</span></div>
        <div>Date <span class="val">{{ date('d/m/Y', strtotime($sale->bill_date)) }}</span></div>
        <div style="color: #000080;">No. <span style="color: #d32f2f; font-size: 16px;">{{ $sale->bill_no }}</span></div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>BRAND NAME</th>
                <th>OPENING</th>
                <th>ISSUE</th>
                <th>RECEIVE</th>
                <th>BALANCE</th>
                <th>RATE</th>
                <th>TOTAL AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $itemsCount = $sale->items->count();
                $maxRows = 48; // Total rows in the sample image
                $salesItems = $sale->items->values();
            @endphp
            
            @for ($i = 0; $i < $maxRows; $i++)
                @php 
                    $item = $i < $itemsCount ? $salesItems[$i] : null; 
                @endphp
                <tr>
                    <td class="col-sno">{{ $i + 1 }}.</td>
                    <td class="col-brand">{{ $item ? ($item->item->name ?? '') : '' }}</td>
                    <td class="col-opening">{{ $item ? round($item->quantity) : '' }}</td>
                    <td class="col-issue"></td>
                    <td class="col-receive"></td>
                    <td class="col-balance"></td>
                    <td class="col-rate">{{ $item ? number_format($item->rate, 2) : '' }}</td>
                    <td class="col-amount">{{ $item ? number_format($item->amount, 2) : '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="footer-grid">
        <div class="footer-left">
            <table class="sub-table" style="height: 100%;">
                <tr>
                    <th colspan="2" class="text-center">Brand Name</th>
                    <th>Less Scheme</th>
                </tr>
                <tr>
                    <td rowspan="3" style="width: 10%; text-align:center; vertical-align:middle;">PI</td>
                    <td>PI Value</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td>PI Qty</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td>Total Value</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">Brand Name</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td rowspan="3" style="width: 10%; text-align:center; vertical-align:middle;">Empty<br>Packet</td>
                    <td>Empty Packet Value</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td>Empty Packet Qty</td>
                    <td class="val-cell"></td>
                </tr>
                <tr>
                    <td>Total Value</td>
                    <td class="val-cell"></td>
                </tr>
            </table>
        </div>
        
        <div class="footer-center">
            <table class="sub-table" style="height: 100%;">
                <tr>
                    <th style="color: #000;">TOTAL AMOUNT</th>
                    <th class="val-cell text-right" style="font-weight: bold;">{{ number_format($sale->total_amount, 2) }}</th>
                </tr>
                <tr>
                    <th style="color: #000;">CASH RECIVED</th>
                    <th class="val-cell"></th>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; border-bottom: 1px solid #000080;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; border-bottom: 1px solid #000080;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; border-bottom: 1px solid #000080;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; border-bottom: 1px solid #000080;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none; border-bottom: 1px solid #000080;">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" style="border:none;">&nbsp;</td>
                </tr>
            </table>
        </div>
        
        <div class="footer-right">
            <table class="cash-table">
                <tr><td>500 X</td><td class="val-cell"></td></tr>
                <tr><td>200 X</td><td class="val-cell"></td></tr>
                <tr><td>100 X</td><td class="val-cell"></td></tr>
                <tr><td>50 X</td><td class="val-cell"></td></tr>
                <tr><td>20 X</td><td class="val-cell"></td></tr>
                <tr><td>10 X</td><td class="val-cell"></td></tr>
                <tr><td>5 X</td><td class="val-cell"></td></tr>
                <tr><td class="text-center" style="text-align:center;">COINS</td><td class="val-cell"></td></tr>
                <tr><td class="text-center" style="text-align:center;">TOTAL</td><td class="val-cell"></td></tr>
            </table>
        </div>
    </div>
</div>

</body>
</html>
