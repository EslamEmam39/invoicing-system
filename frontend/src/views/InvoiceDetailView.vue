<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useAuth } from '../composables/useAuth'
import { useInvoices } from '../composables/useInvoices'
import DataTable, { type TableColumn } from '../components/ui/DataTable.vue'
import BaseAlert from '../components/ui/BaseAlert.vue'
import { useRouter } from '../router'
import type { Invoice } from '../types/invoice'
import type { SalesReturn } from '../types/salesReturn'
import { money } from '../utils/formatters'

const { invoiceId, push } = useRouter()
const { isAdmin } = useAuth()
const store = useInvoices()

const invoice = ref<Invoice | null>(null)
const returns = ref<SalesReturn[]>([])
const returning = ref(false)
const quantities = ref<Record<number, number>>({})
const error = ref('')
const success = ref('')
const itemColumns: TableColumn[] = [
  { key: 'product', label: 'Product' },
  { key: 'quantity', label: 'Qty' },
  { key: 'unit_price', label: 'Price' },
  { key: 'subtotal', label: 'Subtotal' }
]
const invoiceRows = computed(() => (invoice.value?.items ?? []) as Array<Record<string, any>>)

const returnedByItem = computed(() => {
  return returns.value
    .filter((item) => item.invoice_id === invoice.value?.id)
    .flatMap((item) => item.items ?? [])
    .reduce<Record<number, number>>((sum, item) => {
      sum[item.invoice_item_id] = (sum[item.invoice_item_id] ?? 0) + item.quantity
      return sum
    }, {})
})

const remaining = (itemId: number, sold: number) => {
  return sold - (returnedByItem.value[itemId] ?? 0)
}

onMounted(async () => {
  try {
    if (invoiceId.value) {
      [invoice.value, { data: returns.value }] = await Promise.all([
        store.show(invoiceId.value),
        store.returns.index(),
      ])

      invoice.value.items?.forEach((item) => {
        quantities.value[item.id] = 0
      })
    }
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Could not load this invoice.'
  }
})

async function cancel() {
  if (!invoice.value || !confirm('Cancel this invoice?')) {
    return
  }

  try {
    await store.cancel(invoice.value.id)
    invoice.value = await store.show(invoice.value.id)
    success.value = 'Invoice cancelled successfully.'
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Could not cancel invoice.'
  }
}

async function createReturn() {
  if (!invoice.value) {
    return
  }

  const items = Object.entries(quantities.value)
    .filter(([, quantity]) => quantity > 0)
    .map(([invoice_item_id, quantity]) => ({
      invoice_item_id: Number(invoice_item_id),
      quantity,
    }))

  if (!items.length) {
    error.value = 'Select a return quantity.'
    return
  }

  try {
    await store.createReturn({ invoice_id: invoice.value.id, items })
    returning.value = false
    quantities.value = {}
    ;[invoice.value, { data: returns.value }] = await Promise.all([
        store.show(invoice.value.id),
      store.returns.index(),
    ])
    success.value = 'Return created successfully.'
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Could not create return.'
  }
}
</script>

<template>
  <div v-if="invoice">
    <div class="page-header">
      <div>
        <p class="eyebrow">{{ invoice.invoice_number }}</p>
        <h1>{{ invoice.customer?.name }}</h1>
      </div>
      <button class="primary" @click="push('/invoices')">Back</button>
    </div>
    <BaseAlert v-if="error" :message="error" @close="error = ''" />
    <BaseAlert v-if="success" variant="success" :message="success" @close="success = ''" />
    <section class="panel">
      <DataTable :columns="itemColumns" :rows="invoiceRows" row-key="id">
        <template #cell-product="{ row }">{{ row.product?.name }}</template>
        <template #cell-unit_price="{ row }">{{ money(row.unit_price) }}</template>
        <template #cell-subtotal="{ row }">{{ money(row.subtotal) }}</template>
      </DataTable>
      <p class="total">Total <strong>{{ money(invoice.total) }}</strong></p>
      <button v-if="invoice.status !== 'cancelled'" class="primary" @click="returning = true">Create return</button>
      <button v-if="isAdmin && invoice.status !== 'cancelled'" class="danger" @click="cancel">Cancel invoice</button>
    </section>
    <section v-if="returning" class="panel return-panel">
      <h2>Create return</h2>
      <form @submit.prevent="createReturn">
        <label v-for="item in invoice.items" :key="item.id">
          {{ item.product?.name }}
          (remaining: {{ remaining(item.id, item.quantity) }})
          <input v-model.number="quantities[item.id]" min="0" :max="remaining(item.id, item.quantity)" type="number">
        </label>
        <button class="primary">
          Confirm return
        </button>
      </form>
    </section>
  </div>
</template>
