<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesReturnSeeder extends Seeder
{
    public function run(SalesReturnService $service): void
    {
        $examples = [
            ['number' => 'DEMO-RET-001', 'invoice' => 'DEMO-INV-001', 'sku' => 'DEMO-001', 'quantity' => 1],
            ['number' => 'DEMO-RET-002', 'invoice' => 'DEMO-INV-002', 'sku' => 'DEMO-003', 'quantity' => 2],
        ];

        foreach ($examples as $example) {
            DB::transaction(function () use ($service, $example): void {
                if (SalesReturn::where('return_number', $example['number'])->exists()) {
                    return;
                }

                $invoice = Invoice::where('invoice_number', $example['invoice'])->firstOrFail();
                $item = $invoice->items()
                    ->whereHas('product', fn ($query) => $query->where('sku', $example['sku']))
                    ->firstOrFail();

                $return = $service->create($invoice, [
                    'items' => [['invoice_item_id' => $item->id, 'quantity' => $example['quantity']]],
                ]);
                $return->update(['return_number' => $example['number']]);
            });
        }
    }
}
