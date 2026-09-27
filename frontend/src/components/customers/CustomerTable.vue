<script setup lang="ts">
import type { Customer } from '../../types/customer'
import BaseButton from '../ui/BaseButton.vue'
import BaseTable from '../ui/BaseTable.vue'
defineProps<{ customers: Customer[]; isAdmin: boolean; loading?: boolean }>()
defineEmits<{ edit: [customer: Customer]; remove: [customer: Customer] }>()
</script>
<template><BaseTable :loading="loading" :empty="!customers.length" empty-message="No customers found."><thead><tr><th>Customer</th><th>Phone</th><th>Address</th><th>Status</th><th></th></tr></thead><tbody><tr v-for="customer in customers" :key="customer.id"><td>{{customer.name}}</td><td>{{customer.phone || '—'}}</td><td>{{customer.address || '—'}}</td><td>{{customer.is_active?'Active':'Inactive'}}</td><td class="actions"><button @click="$emit('edit',customer)">Edit</button><BaseButton v-if="isAdmin" variant="danger" @click="$emit('remove',customer)">Delete</BaseButton></td></tr></tbody></BaseTable></template>
