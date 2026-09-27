<script setup lang="ts">
import { onMounted, ref } from 'vue'
import InvoiceForm from '../components/invoices/InvoiceForm.vue'
import InvoiceTable from '../components/invoices/InvoiceTable.vue'
import AppHeader from '../components/layout/AppHeader.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import BasePagination from '../components/ui/BasePagination.vue'
import BaseAlert from '../components/ui/BaseAlert.vue'
import { useCustomers } from '../composables/useCustomers'
import { useInvoices } from '../composables/useInvoices'
import { useProducts } from '../composables/useProducts'
import { useRouter } from '../router'
import type { Invoice, InvoicePayload } from '../types/invoice'

const invoices = useInvoices()
const customers = useCustomers()
const products = useProducts()
const { push } = useRouter()
const isFormOpen = ref(false)
const error = ref('')
const success = ref('')
const loading = ref(false)

onMounted(load)

async function load() {
  loading.value = true
  try {
    await Promise.all([invoices.load(), customers.load(), products.load()])
  } catch (exception) {
    showError(exception)
  } finally {
    loading.value = false
  }
}
function openCreate() {
  isFormOpen.value = true
}
function closeForm() {
  isFormOpen.value = false
}
async function create(payload: InvoicePayload) {
  try {
    await invoices.create(payload)
    closeForm()
    await Promise.all([invoices.load(), products.load()])
    success.value = 'Invoice created successfully.'
  } catch (exception) {
    showError(exception)
  }
}
function openInvoice(invoice: Invoice) {
  push(`/invoices/${invoice.id}`)
}
function showError(exception: unknown) {
  error.value = exception instanceof Error ? exception.message : 'The request could not be completed.'
}
</script>

<template>
  <AppHeader title="Invoices"><template #action>
      <BaseButton @click="openCreate">+ New invoice</BaseButton>
    </template>
  </AppHeader>
  <BaseAlert v-if="error" :message="error" @close="error = ''" />
  <BaseAlert v-if="success" variant="success" :message="success" @close="success = ''" />
  <InvoiceTable :invoices="invoices.invoices.value" :loading="loading" @view="openInvoice" />
  <BasePagination :page="invoices.pagination.value.current_page" :last-page="invoices.pagination.value.last_page"
    :total="invoices.pagination.value.total" @change="invoices.load" />
  <InvoiceForm v-if="isFormOpen" :customers="customers.customers.value" :products="products.activeProducts.value"
    @close="closeForm" @submit="create" />
</template>
