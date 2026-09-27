<script setup lang="ts">
import type { Product } from '../../types/product'
import BaseButton from '../ui/BaseButton.vue'
import BaseTable from '../ui/BaseTable.vue'
defineProps<{ products: Product[]; isAdmin: boolean; loading?: boolean }>()
defineEmits<{ edit: [product: Product]; remove: [product: Product] }>()
</script>

<template>
  <BaseTable :loading="loading" :empty="!products.length" empty-message="No products found.">
    <thead>
      <tr>
        <th>Product</th>
        <th>SKU</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="product in products" :key="product.id">
        <td>{{ product.name }}</td>
        <td>{{ product.sku }}</td>
        <td>{{ product.price }}</td>
        <td>{{ product.stock }}</td>
        <td>{{ product.is_active ? 'Active' : 'Inactive' }}</td>
        <td class="actions"><button @click="$emit('edit', product)">Edit</button>
          <BaseButton v-if="isAdmin" variant="danger" @click="$emit('remove', product)">Delete</BaseButton>
        </td>
      </tr>
    </tbody>
  </BaseTable>
</template>
