<script setup lang="ts">
import { onMounted, ref } from 'vue'
import BasePagination from '../components/ui/BasePagination.vue'
import BaseTable from '../components/ui/BaseTable.vue'
import { salesReturnService } from '../services/salesReturnService'
import type { SalesReturn } from '../types/salesReturn'
import { date, money } from '../utils/formatters'

const returns = ref<SalesReturn[]>([])
const error = ref('')
const loading = ref(false)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

onMounted(load)

async function load(page = pagination.value.current_page) {
  loading.value = true

  try {
    const result = await salesReturnService.index(page)
    returns.value = result.data
    pagination.value = result.meta ?? { current_page: page, last_page: 1, total: result.data.length }
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Could not load returns.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page-header">
    <h1>Returns</h1>
  </div>
  <p v-if="error" class="notice error">{{ error }}</p>
  <BaseTable :loading="loading" :empty="!returns.length" empty-message="No returns found.">
    <thead>
      <tr>
        <th>Return</th>
        <th>Invoice ID</th>
        <th>Returned</th>
        <th>Items</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in returns" :key="item.id">
        <td>{{ item.return_number }}</td>
        <td>#{{ item.invoice_id }}</td>
        <td>{{ date(item.returned_at) }}</td>
        <td>{{ item.items?.length ?? 0 }}</td>
        <td>{{ item.total ? money(item.total) : '—' }}</td>
      </tr>
    </tbody>
  </BaseTable>
  <BasePagination :page="pagination.current_page" :last-page="pagination.last_page" :total="pagination.total"
    @change="load" />
</template>
