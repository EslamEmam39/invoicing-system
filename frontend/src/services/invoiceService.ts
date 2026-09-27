import type { Invoice, InvoicePayload } from '../types/invoice'
import { http, httpPaginated, json } from './httpClient'
export const invoiceService = {
  index: (page = 1, perPage = 15) => httpPaginated<Invoice>(`/invoices?page=${page}&per_page=${perPage}`),
  show: (id: number) => http<Invoice>(`/invoices/${id}`),
  create: (body: InvoicePayload) => http<Invoice>('/invoices', json('POST', body)),
  cancel: (id: number) => http<Invoice>(`/admin/invoices/${id}/cancel`, json('POST')),
}
