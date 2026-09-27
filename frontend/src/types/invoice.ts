import type { Customer } from './customer'
import type { Product } from './product'
export type InvoiceItem = { id: number; product_id: number; product?: Product; quantity: number; unit_price: string; subtotal: string }
export type Invoice = {
    id: number;
    invoice_number: string;
    customer_id: number;
    status: string;
    total: string;
    issued_at: string;
    customer?: Customer;
    items?: InvoiceItem[]
}
export type InvoicePayload = { customer_id: number; items: { product_id: number; quantity: number }[] }
