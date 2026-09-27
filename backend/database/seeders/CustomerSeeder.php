<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Demo Customer 01', 'phone' => '01000000001', 'address' => 'Cairo'],
            ['name' => 'Demo Customer 02', 'phone' => '01000000002', 'address' => 'Giza'],
            ['name' => 'Demo Customer 03', 'phone' => '01000000003', 'address' => 'Alexandria'],
            ['name' => 'Demo Customer 04', 'phone' => '01000000004', 'address' => 'Mansoura'],
            ['name' => 'Demo Customer 05', 'phone' => '01000000005', 'address' => 'Tanta'],
            ['name' => 'Demo Customer 06', 'phone' => '01000000006', 'address' => 'Zagazig'],
            ['name' => 'Demo Customer 07', 'phone' => '01000000007', 'address' => 'Ismailia'],
            ['name' => 'Demo Customer 08', 'phone' => '01000000008', 'address' => 'Suez'],
            ['name' => 'Demo Customer 09', 'phone' => '01000000009', 'address' => null],
            ['name' => 'Demo Customer 10', 'phone' => null, 'address' => null],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['name' => $customer['name']],
                [...$customer, 'is_active' => true],
            );
        }
    }
}
