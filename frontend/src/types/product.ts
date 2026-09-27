export type Product = { id: number; name: string; sku: string; price: string; stock: number; is_active: boolean }
export type ProductPayload = Omit<Product, 'id'>
