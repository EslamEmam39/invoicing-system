<?php

namespace App\Http\Requests\Api\SalesReturn;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;

class StoreSalesReturnRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Avoid passing malformed array input into the scoped database rule.
        $invoiceId = filter_var($this->input('invoice_id'), FILTER_VALIDATE_INT) ?: 0;

        return [
            'invoice_id' => ['bail', 'required', 'integer', Rule::exists('invoices', 'id')->whereNull('deleted_at')],

            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array:invoice_item_id,quantity'],
            'items.*.invoice_item_id' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::exists('invoice_items', 'id')->where('invoice_id', $invoiceId),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ];
    }
}
