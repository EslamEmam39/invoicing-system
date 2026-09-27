<script setup lang="ts">
import { onMounted, ref } from 'vue'
import CustomerForm from '../components/customers/CustomerForm.vue'
import CustomerTable from '../components/customers/CustomerTable.vue'
import AppHeader from '../components/layout/AppHeader.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import BasePagination from '../components/ui/BasePagination.vue'
import BaseAlert from '../components/ui/BaseAlert.vue'
import { useAuth } from '../composables/useAuth'
import { useCustomers } from '../composables/useCustomers'
import type { Customer, CustomerPayload } from '../types/customer'

const store = useCustomers()
const { isAdmin } = useAuth()

const editingCustomer = ref<Customer | null>(null)
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

function changePerPage(perPage: number) {
    load(1, perPage)
}

function openCreate() {
    editingCustomer.value = null
    isFormOpen.value = true
}

function openEdit(customer: Customer) {
    editingCustomer.value = customer
    isFormOpen.value = true
}

function closeForm() {
    isFormOpen.value = false
    editingCustomer.value = null
}

async function save(payload: CustomerPayload) {
    try {
        await store.save(payload, editingCustomer.value?.id)
        closeForm()
        await load()
        success.value = 'Customer saved successfully.'
    } catch (exception) {
        showError(exception)
    }
}

async function remove(customer: Customer) {
    if (!confirm(`Delete ${customer.name}?`)) {
        return
    }

    try {
        await store.remove(customer.id)
        await load()
        success.value = 'Customer deleted successfully.'
    } catch (exception) {
        showError(exception)
    }
}

function showError(exception: unknown) {
    error.value = exception instanceof Error
        ? exception.message
        : 'The request could not be completed.'
}
</script>

<template>
    <AppHeader title="Customers">
        <template #action>
            <BaseButton @click="openCreate">+ Add customer</BaseButton>
        </template>
    </AppHeader>

    <BaseAlert v-if="error" :message="error" @close="error = ''" />
    <BaseAlert v-if="success" variant="success" :message="success" @close="success = ''" />

    <CustomerTable :customers="store.customers.value" :is-admin="isAdmin" :loading="loading" @edit="openEdit"
        @remove="remove" />

    <BasePagination :page="store.pagination.value.current_page" :last-page="store.pagination.value.last_page"
        :total="store.pagination.value.total" :per-page="store.perPage.value" @change="load"
        @per-page-change="changePerPage" />

    <CustomerForm v-if="isFormOpen" :customer="editingCustomer" @close="closeForm" @submit="save" />
</template>
