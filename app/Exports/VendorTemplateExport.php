<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Sample Vendor', '9876543210', '456 Oak Ave, City', 'Active'],
        ];
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Address', 'Status'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
