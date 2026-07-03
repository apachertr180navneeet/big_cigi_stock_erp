<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\ItemMaster;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PurchaseCalculationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_purchase_calculation_matches_specification(): void
    {
        // 1. Create admin user
        $admin = User::forceCreate([
            'first_name' => "Test",
            'last_name' => "Admin",
            'full_name' => "Test Admin",
            'slug' => "test-admin",
            'email' => 'testadmin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '1234567890',
            'country' => 'Australia',
            'city' => 'Sydney',
            'state' => 'NSW',
            'zipcode' => '2000',
            'country_code' => '1',
            'status' => 'active',
        ]);

        // 2. Create vendor
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'status' => 1,
        ]);

        // 3. Create item
        $item = ItemMaster::create([
            'name' => 'Test Cigi',
            'status' => 1,
        ]);

        // 4. Act: post a purchase creation request
        $postData = [
            'vendor_id' => $vendor->id,
            'bill_no' => 'BILL-12345',
            'bill_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'no_of_package' => 0.33,
                    'uom' => 'M_S',
                    'quantity' => 4,
                    'rate' => 3525.64,
                    'discount_amount' => 0.00,
                    'packets' => 400,
                    'mrp' => 58,
                    'cgst_rate' => 20.00,
                    'sgst_rate' => 20.00,
                ]
            ]
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $postData);

        // 5. Assert redirect after success
        $response->assertRedirect(route('admin.purchases.index'));

        // 6. Verify calculations in DB
        $purchase = Purchase::where('bill_no', 'BILL-12345')->first();
        $this->assertNotNull($purchase);

        // Expected values:
        // Basic Value = 4 * 3525.64 = 14102.56
        // Net Amount = 14102.56 - 0 = 14102.56
        // T/V = 400 * 58 = 23200
        // Taxable Value = round(23200 / 1.40, 2) = 16571.43
        // CGST = round(16571.43 * 0.20, 2) = 3314.29
        // SGST = round(16571.43 * 0.20, 2) = 3314.29
        // Tax Amount = 3314.29 + 3314.29 = 6628.58
        // Total = 14102.56 + 6628.58 = 20731.14

        $this->assertEquals(20731.14, $purchase->total_amount);

        $purchaseItem = PurchaseItem::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($purchaseItem);
        $this->assertEquals(0.33, $purchaseItem->no_of_package);
        $this->assertEquals('M_S', $purchaseItem->uom);
        $this->assertEquals(4.00, $purchaseItem->quantity);
        $this->assertEquals(3525.64, $purchaseItem->rate);
        $this->assertEquals(0.00, $purchaseItem->discount_amount);
        $this->assertEquals(400.00, $purchaseItem->packets);
        $this->assertEquals(58.00, $purchaseItem->mrp);
        $this->assertEquals(16571.43, $purchaseItem->taxable_value);
        $this->assertEquals(20.00, $purchaseItem->cgst_rate);
        $this->assertEquals(3314.29, $purchaseItem->cgst_amount);
        $this->assertEquals(20.00, $purchaseItem->sgst_rate);
        $this->assertEquals(3314.29, $purchaseItem->sgst_amount);
        $this->assertEquals(6628.58, $purchaseItem->tax_amount);
        $this->assertEquals(20731.14, $purchaseItem->amount);
    }
}
