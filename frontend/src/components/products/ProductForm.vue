<script setup lang="ts">
import { reactive, watch } from 'vue'
import type { Product, ProductPayload } from '../../types/product'
import BaseButton from '../ui/BaseButton.vue'
import BaseInput from '../ui/BaseInput.vue'
import BaseModal from '../ui/BaseModal.vue'

const props = defineProps<{ product: Product | null }>()
const emit = defineEmits<{ close: []; submit: [payload: ProductPayload] }>()
const form = reactive({ name: '', sku: '', price: '', stock: '', is_active: true })
function sync(product: Product | null) { Object.assign(form, product ? { ...product, price: String(product.price), stock: String(product.stock) } : { name: '', sku: '', price: '', stock: '', is_active: true }) }
watch(() => props.product, sync, { immediate: true })
function submit() { emit('submit', { name: form.name, sku: form.sku, price: form.price, stock: Number(form.stock), is_active: form.is_active }) }
</script>

<template>
  <BaseModal :title="product ? 'Edit product' : 'Add product'" @close="emit('close')">
    <h2>{{ product ? 'Edit product' : 'Add product' }}</h2>
    <form @submit.prevent="submit">
      <BaseInput v-model="form.name" label="Name" required />
      <BaseInput v-model="form.sku" label="SKU" required />
      <BaseInput v-model="form.price" label="Price" type="number" min="0" step="0.01" required />
      <BaseInput v-model="form.stock" label="Stock" type="number" min="0" step="1" required />
      <label class="check"><input v-model="form.is_active" type="checkbox">Active product</label>
      <BaseButton type="submit">Save product</BaseButton>
    </form>
  </BaseModal>
</template>
