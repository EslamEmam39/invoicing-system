<script setup lang="ts">
import { onMounted, ref } from 'vue'
import ProductForm from '../components/products/ProductForm.vue'
import ProductTable from '../components/products/ProductTable.vue'
import AppHeader from '../components/layout/AppHeader.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import BasePagination from '../components/ui/BasePagination.vue'
import BaseAlert from '../components/ui/BaseAlert.vue'
import { useAuth } from '../composables/useAuth'
import { useProducts } from '../composables/useProducts'
import type { Product, ProductPayload } from '../types/product'

const store = useProducts()
const { isAdmin } = useAuth()
const editingProduct = ref<Product | null>(null)
const isFormOpen = ref(false)
const error = ref('')
const success = ref('')
const loading = ref(false)

onMounted(load)

async function load(page?: number, perPage?: number) {
    loading.value = true

    try {
        await store.load(page, perPage)
    } catch (exception) {
        showError(exception)
    } finally {
        loading.value = false
    }
}
async function changePerPage(perPage: number) {
    await load(1, perPage)
}
function openCreate() {
    editingProduct.value = null
    isFormOpen.value = true
}
function openEdit(product: Product) {
    editingProduct.value = product
    isFormOpen.value = true
}
function closeForm() {
    isFormOpen.value = false
    editingProduct.value = null
}
async function save(payload: ProductPayload) {
    try {
    await store.save(payload, editingProduct.value?.id)
    closeForm()
    await load()
    success.value = 'Product saved successfully.'
    } catch (exception) {
        showError(exception)
    }
}
async function remove(product: Product) {
    if (!confirm(`Delete ${product.name}?`)) {
        return
    }
    try {
    await store.remove(product.id)
    await load()
    success.value = 'Product deleted successfully.'
    } catch (exception) {
        showError(exception)
    }
}
function showError(exception: unknown) {
    error.value = exception instanceof Error ? exception.message : 'The request could not be completed.'
}
</script>

<template>
    <AppHeader title="Products"><template #action>
            <BaseButton @click="openCreate">+ Add product</BaseButton>
        </template>
    </AppHeader>
  <BaseAlert v-if="error" :message="error" @close="error = ''" />
  <BaseAlert v-if="success" variant="success" :message="success" @close="success = ''" />
    <ProductTable :products="store.products.value" :is-admin="isAdmin" :loading="loading" @edit="openEdit"
        @remove="remove" />
    <BasePagination :page="store.pagination.value.current_page" :last-page="store.pagination.value.last_page"
        :total="store.pagination.value.total" :per-page="store.perPage.value" @change="load"
        @per-page-change="changePerPage" />
    <ProductForm v-if="isFormOpen" :product="editingProduct" @close="closeForm" @submit="save" />
</template>
