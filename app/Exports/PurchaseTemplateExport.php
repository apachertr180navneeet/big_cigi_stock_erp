<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['1', 'RoyaI10BE', '20.00 PCS', '33.67', 'PCS', '0', '202013.40'],
            ['2', 'GF SUPER STAR 10BE', '50.00 PCS', '48.81', 'PCS', '0', '19525.64'],
            ['3', 'CLASSIC FINE TEST 20BE', '20.00 PCS', '237.04', 'PCS', '0', '5940.76'],
        ];
    }

    public function headings(): array
    {
        return [
            'Sl No.',
            'Description of Goods',
            'Quantity',
            'Rate',
            'per',
            'Disc. %',
            'Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
