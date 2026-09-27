export type SalesReturnPayload = { invoice_id: number; items: { invoice_item_id: number; quantity: number }[] }
export type ReturnItem = { id: number; invoice_item_id: number; quantity: number; unit_price: string; subtotal: string }
export type SalesReturn = {
    id: number;
    return_number: string;
    invoice_id: number;
    returned_at: string;
    total?: string;
    items?: ReturnItem[];
    created_at: string
}
