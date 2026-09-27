<script setup lang="ts">
defineProps<{ title?: string; wide?: boolean }>()
defineEmits<{ close: [] }>()
</script>
<template>
    <div class="modal-backdrop" @click.self="$emit('close')">
        <section class="modal" :class="{ 'wide-modal': wide }" role="dialog" aria-modal="true" :aria-label="title">
            <button class="close" type="button" aria-label="Close dialog" @click="$emit('close')">×</button>
            <slot />
        </section>
    </div>
</template>
