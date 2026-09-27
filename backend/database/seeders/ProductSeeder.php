<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'DEMO-001', 'name' => 'Wireless Mouse', 'price' => '350.00', 'stock' => 50],
            ['sku' => 'DEMO-002', 'name' => 'USB Keyboard', 'price' => '450.00', 'stock' => 35],
            ['sku' => 'DEMO-003', 'name' => '24-inch Monitor', 'price' => '4500.00', 'stock' => 15],
            ['sku' => 'DEMO-004', 'name' => 'USB-C Cable', 'price' => '150.00', 'stock' => 100],
            ['sku' => 'DEMO-005', 'name' => 'Laptop Stand', 'price' => '650.00', 'stock' => 25],
            ['sku' => 'DEMO-006', 'name' => 'Headset', 'price' => '800.00', 'stock' => 20],
            ['sku' => 'DEMO-007', 'name' => 'Webcam', 'price' => '1200.00', 'stock' => 10],
            ['sku' => 'DEMO-008', 'name' => 'USB Hub', 'price' => '550.00', 'stock' => 30],
            ['sku' => 'DEMO-009', 'name' => 'External SSD', 'price' => '3200.00', 'stock' => 12],
            ['sku' => 'DEMO-010', 'name' => 'Mouse Pad', 'price' => '100.00', 'stock' => 0],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [...$product, 'is_active' => true],
            );
        }
    }
}
