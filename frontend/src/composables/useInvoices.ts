import { ref } from 'vue'
import { invoiceService } from '../services/invoiceService'
import { salesReturnService } from '../services/salesReturnService'
import type { Invoice, InvoicePayload } from '../types/invoice'
import type { SalesReturnPayload } from '../types/salesReturn'
const invoices = ref<Invoice[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
export function useInvoices() {
  async function load(page = pagination.value.current_page) {
    const result = await invoiceService.index(page)
    invoices.value = result.data
    pagination.value = result.meta ?? { current_page: page, last_page: 1, total: result.data.length }
  }
  return {
    invoices,
    pagination,
    load,
    show: invoiceService.show,
    create: (payload: InvoicePayload) => invoiceService.create(payload),
    cancel: invoiceService.cancel,
    createReturn: (payload: SalesReturnPayload) => salesReturnService.create(payload),
    returns: salesReturnService,
  }
}
