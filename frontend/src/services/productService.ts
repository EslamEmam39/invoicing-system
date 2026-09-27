import type { Product, ProductPayload } from '../types/product'
import { http, httpPaginated, json } from './httpClient'
export const productService = {
  index: (page = 1, perPage = 15) => httpPaginated<Product>(`/products?page=${page}&per_page=${perPage}`),
  create: (body: ProductPayload) => http<Product>('/products', json('POST', body)),
  update: (id: number, body: ProductPayload) => http<Product>(`/products/${id}`, json('PATCH', body)),
  remove: (id: number) => http<null>(`/admin/products/${id}`, { method: 'DELETE' }),
}
