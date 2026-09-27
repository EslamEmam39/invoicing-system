import { computed, ref } from 'vue'
import { customerService } from '../services/customerService'
import type { Customer, CustomerPayload } from '../types/customer'
const customers = ref<Customer[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const perPage = ref(15)

export function useCustomers() {
  const activeCustomers = computed(() => customers.value.filter((item) => item.is_active))
  async function load(page = pagination.value.current_page, requestedPerPage = perPage.value) {
    perPage.value = requestedPerPage
    const result = await customerService.index(page, perPage.value)
    customers.value = result.data
    pagination.value = result.meta ?? { current_page: page, last_page: 1, total: result.data.length }
  }
  return {
    customers, activeCustomers, pagination, perPage, load, save: (payload: CustomerPayload, id?: number) => id
      ? customerService.update(id, payload)
      : customerService.create(payload), remove: customerService.remove
  }
}
