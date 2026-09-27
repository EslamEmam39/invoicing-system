<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from './components/layout/AppLayout.vue'
import AppSidebar from './components/layout/AppSidebar.vue'
import { useAuth } from './composables/useAuth'
import { useRouter } from './router'
import LoginView from './views/LoginView.vue'
import DashboardView from './views/DashboardView.vue'
import ProductsView from './views/ProductsView.vue'
import CustomersView from './views/CustomersView.vue'
import InvoicesView from './views/InvoicesView.vue'
import InvoiceDetailView from './views/InvoiceDetailView.vue'
import ReturnsView from './views/ReturnsView.vue'
import NotFoundView from './views/NotFoundView.vue'

const { isAuthenticated } = useAuth()
const { route } = useRouter()
const view = computed(() => ({
  dashboard: DashboardView,
  products: ProductsView,
  customers: CustomersView,
  invoices: InvoicesView,
  returns: ReturnsView, 'invoice-detail': InvoiceDetailView,
  'not-found': NotFoundView
}[route.value]))
</script>

<template>
  <LoginView v-if="!isAuthenticated" />
  <AppLayout v-else>
    <template #sidebar>
      <AppSidebar />
    </template>
    <component :is="view" />
  </AppLayout>
</template>
