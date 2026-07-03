<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemMasterTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Sample Item', 'Sample description', '12345678', 'BR001', '50', '100', '12', 'Active'],
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Description',
            'HSN',
            'Brand Code',
            'Opening Stock',
            'Current Stock',
            'Pack Size',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
