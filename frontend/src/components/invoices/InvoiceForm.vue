<script setup lang="ts">
import { computed, reactive } from 'vue'
import type { Customer } from '../../types/customer'
import type { InvoicePayload } from '../../types/invoice'
import type { Product } from '../../types/product'
import { money } from '../../utils/formatters'
import BaseButton from '../ui/BaseButton.vue'
import BaseModal from '../ui/BaseModal.vue'

const props = defineProps<{ customers: Customer[]; products: Product[] }>()
const emit = defineEmits<{ close: []; submit: [payload: InvoicePayload] }>()
const form = reactive<{ customerId: string; items: { productId: string; quantity: number }[] }>({ customerId: '', items: [{ productId: '', quantity: 1 }] })
const total = computed(() => form.items.reduce((sum, line) => sum + Number(props.products.find(product => product.id === Number(line.productId))?.price ?? 0) * line.quantity, 0))
function addLine() { form.items.push({ productId: '', quantity: 1 }) }
function removeLine(index: number) { if (form.items.length > 1) form.items.splice(index, 1) }
function isSelected(productId: number, currentLine: number) { return form.items.some((line, index) => index !== currentLine && Number(line.productId) === productId) }
function submit() { emit('submit', { customer_id: Number(form.customerId), items: form.items.map(line => ({ product_id: Number(line.productId), quantity: line.quantity })) }) }
</script>

<template>
  <BaseModal title="Create invoice" wide @close="emit('close')">
    <h2>Create invoice</h2>
    <form @submit.prevent="submit">
      <label>Customer<select v-model="form.customerId" required>
          <option value="" disabled>Select a customer</option>
          <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
        </select></label>
      <div class="line-items">
        <div v-for="(line, index) in form.items" :key="index" class="line">
          <select v-model="line.productId" required>
            <option value="" disabled>Select a product</option>
            <option v-for="product in products" :key="product.id" :value="product.id"
              :disabled="isSelected(product.id, index)">{{ product.name }} · {{ product.stock }} available</option>
          </select>
          <input v-model.number="line.quantity" required min="1"
            :max="products.find(product => product.id === Number(line.productId))?.stock" type="number">
          <span>{{money(Number(products.find(product => product.id === Number(line.productId))?.price ?? 0) *
            line.quantity)}}</span>
          <button type="button" :disabled="form.items.length === 1" aria-label="Remove line"
            @click="removeLine(index)">×</button>
        </div>
      </div>
      <BaseButton variant="text" type="button" @click="addLine">+ Add line</BaseButton>
      <p class="total">Total <strong>{{ money(total) }}</strong></p>
      <BaseButton type="submit">Create invoice</BaseButton>
    </form>
  </BaseModal>
</template>
