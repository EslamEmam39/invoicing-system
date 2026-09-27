<script setup lang="ts">
import type { Invoice } from '../../types/invoice'
import { money } from '../../utils/formatters'
import BaseTable from '../ui/BaseTable.vue'
defineProps<{ invoices: Invoice[]; loading?: boolean }>()
defineEmits<{ view: [invoice: Invoice] }>()
</script>

<template>
  <BaseTable :loading="loading" :empty="!invoices.length" empty-message="No invoices found.">
    <thead><tr><th>Invoice</th><th>Customer</th><th>Issued</th><th>Status</th><th>Total</th><th></th></tr></thead>
    <tbody>
      <tr v-for="invoice in invoices" :key="invoice.id">
        <td><strong>{{ invoice.invoice_number }}</strong></td><td>{{ invoice.customer?.name ?? '—' }}</td><td>{{ new Date(invoice.issued_at).toLocaleDateString() }}</td>
        <td><span class="status" :class="invoice.status">{{ invoice.status }}</span></td><td>{{ money(invoice.total) }}</td>
        <td><button @click="$emit('view', invoice)">View</button></td>
      </tr>
    </tbody>
  </BaseTable>
</template>
