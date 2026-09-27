<script setup lang="ts">
withDefaults(defineProps<{ loading?: boolean; empty?: boolean; emptyMessage?: string }>(),
    { loading: false, empty: false, emptyMessage: 'No records found.' })
</script>
<template>
    <section class="panel">
        <p v-if="loading" class="loading">Loading…</p>
        <p v-else-if="empty" class="empty">{{ emptyMessage }}</p>
        <table v-else>
            <slot />
        </table>
    </section>
</template>
