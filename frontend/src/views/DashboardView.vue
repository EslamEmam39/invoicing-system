<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useCustomers } from '../composables/useCustomers'
import { useInvoices } from '../composables/useInvoices'
import { useProducts } from '../composables/useProducts'
import { money } from '../utils/formatters'

const products = useProducts()
const customers = useCustomers()
const invoices = useInvoices()

const total = computed(() =>
  invoices.invoices.value
    .filter((invoice) => invoice.status !== 'cancelled')
    .reduce((sum, invoice) => sum + Number(invoice.total), 0),
)

onMounted(() => Promise.all([products.load(), customers.load(), invoices.load()]))
</script>

<template>
  <div class="stats">
    <article><small>Active products</small><strong>{{ products.activeProducts.value.length }}</strong></article>
    <article><small>Active customers</small><strong>{{ customers.activeCustomers.value.length }}</strong></article>
    <article><small>Invoices</small><strong>{{ invoices.invoices.value.length }}</strong></article>
    <article><small>Sales recorded</small><strong>{{ money(total) }}</strong></article>
  </div>
</template>
