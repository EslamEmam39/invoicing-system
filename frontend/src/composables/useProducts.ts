import { computed, ref } from 'vue'
import { productService } from '../services/productService'
import type { Product, ProductPayload } from '../types/product'
const products = ref<Product[]>([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const perPage = ref(15)
export function useProducts() {
  const activeProducts = computed(() => products.value.filter((item) => item.is_active))
  async function load(page = pagination.value.current_page, requestedPerPage = perPage.value) {
    perPage.value = requestedPerPage
    const result = await productService.index(page, perPage.value)
    products.value = result.data
    pagination.value = result.meta ?? { current_page: page, last_page: 1, total: result.data.length }
  }
  return {
    products, activeProducts, pagination, perPage, load, save: (payload: ProductPayload, id?: number) => id
      ? productService.update(id, payload)
      : productService.create(payload), remove: productService.remove
  }
}
