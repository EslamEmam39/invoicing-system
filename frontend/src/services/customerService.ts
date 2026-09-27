import type { Customer, CustomerPayload } from '../types/customer'
import { http, httpPaginated, json } from './httpClient'
export const customerService = {
  index: (page = 1, perPage = 15) => httpPaginated<Customer>(`/customers?page=${page}&per_page=${perPage}`),
  create: (body: CustomerPayload) => http<Customer>('/customers', json('POST', body)),
  update: (id: number, body: CustomerPayload) => http<Customer>(`/customers/${id}`, json('PATCH', body)),
  remove: (id: number) => http<null>(`/admin/customers/${id}`, { method: 'DELETE' }),
}
