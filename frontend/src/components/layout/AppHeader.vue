<script setup lang="ts">defineProps<{ title: string; eyebrow?: string }>()</script>
<template>
    <header class="page-header">
        <div>
            <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
            <h1>{{ title }}</h1>
        </div>
        <slot name="action" />
    </header>
</template>
