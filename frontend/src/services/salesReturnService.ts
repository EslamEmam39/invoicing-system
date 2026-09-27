import type { SalesReturn, SalesReturnPayload } from '../types/salesReturn'
import { http, httpPaginated, json } from './httpClient'
export const salesReturnService = {
  index: (page = 1, perPage = 15) => httpPaginated<SalesReturn>(`/returns?page=${page}&per_page=${perPage}`),
  show: (id: number) => http<SalesReturn>(`/returns/${id}`),
  create: (body: SalesReturnPayload) => http<SalesReturn>('/returns', json('POST', body)),
}
