<?php

namespace App\Imports;

use App\Models\ItemMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ItemMasterImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new ItemMaster([
            'name'        => $row['name'],
            'description' => $row['description'] ?? null,
            'hsn'         => $row['hsn'] ?? null,
            'brand_code'  => $row['brand_code'] ?? null,
            'current_stock' => $row['current_stock'] ?? 0,
            'status'      => isset($row['status']) && strtolower($row['status']) === 'inactive' ? 0 : 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required for each row.',
        ];
    }
}
