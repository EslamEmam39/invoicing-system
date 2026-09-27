<script setup lang="ts">
import { useRouter } from '../../router'
import { useAuth } from '../../composables/useAuth'
const { push, route } = useRouter()
const { user, logout } = useAuth()
const links = [{ path: '/', label: 'Overview', icon: '⌂', route: 'dashboard' }, { path: '/products', label: 'Products', icon: '▣', route: 'products' }, { path: '/customers', label: 'Customers', icon: '♙', route: 'customers' }, { path: '/invoices', label: 'Invoices', icon: '▤', route: 'invoices' }, { path: '/returns', label: 'Returns', icon: '↩', route: 'returns' }]
async function signOut() { await logout(); push('/') }
</script>
<template>
    <aside class="app-sidebar">
        <div class="brand"><span>◆</span> Invoice Flow</div>
        <nav aria-label="Main navigation"><button v-for="link in links" :key="link.path"
                :class="{ active: route === link.route }" :aria-current="route === link.route ? 'page' : undefined"
                @click="push(link.path)"><b>{{ link.icon }}</b>{{ link.label }}</button></nav>
        <div class="profile"><span class="avatar">{{ user?.name?.slice(0, 1).toUpperCase() }}</span>
            <div><strong>{{ user?.name }}</strong><small>{{ user?.role }}</small></div><button class="logout"
                type="button" title="Sign out" aria-label="Sign out" @click="signOut">⇥</button>
        </div>
    </aside>
</template>
