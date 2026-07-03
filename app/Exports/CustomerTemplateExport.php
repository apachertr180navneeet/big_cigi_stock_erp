<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Sample Customer', '1234567890', '123 Main St, City', 'Active'],
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
