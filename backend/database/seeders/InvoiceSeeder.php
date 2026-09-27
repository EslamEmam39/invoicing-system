<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run(InvoiceService $service): void
    {
        // Stable demo numbers distinguish seeded records from real transactions.
        $examples = [
            ['number' => 'DEMO-INV-001', 'email' => 'employee@invoicing.test', 'customer' => 'Demo Customer 01', 'items' => ['DEMO-001' => 4, 'DEMO-002' => 2], 'cancel' => false],
            ['number' => 'DEMO-INV-002', 'email' => 'admin@invoicing.test', 'customer' => 'Demo Customer 02', 'items' => ['DEMO-003' => 2], 'cancel' => false],
            ['number' => 'DEMO-INV-003', 'email' => 'employee@invoicing.test', 'customer' => 'Demo Customer 03', 'items' => ['DEMO-004' => 5], 'cancel' => true],
            ['number' => 'DEMO-INV-004', 'email' => 'admin@invoicing.test', 'customer' => 'Demo Customer 04', 'items' => ['DEMO-005' => 2, 'DEMO-006' => 1], 'cancel' => false],
        ];

        foreach ($examples as $example) {
            DB::transaction(function () use ($service, $example): void {
                if (Invoice::withTrashed()->where('invoice_number', $example['number'])->exists()) {
                    return;
                }

                $user = User::where('email', $example['email'])->firstOrFail();
                $customer = Customer::where('name', $example['customer'])->firstOrFail();
                $items = [];
                foreach ($example['items'] as $sku => $quantity) {
                    $items[] = [
                        'product_id' => Product::where('sku', $sku)->firstOrFail()->id,
                        'quantity' => $quantity,
                    ];
                }

                $invoice = $service->create(['customer_id' => $customer->id, 'items' => $items], $user);
                $invoice->update(['invoice_number' => $example['number']]);
                if ($example['cancel']) {
                    $service->cancel($invoice);
                }
            });
        }
    }
}
