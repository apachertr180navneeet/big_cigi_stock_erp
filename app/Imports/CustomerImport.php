<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Customer([
            'name'    => $row['name'],
            'phone'   => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'status'  => isset($row['status']) && strtolower($row['status']) === 'inactive' ? 0 : 1,
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
