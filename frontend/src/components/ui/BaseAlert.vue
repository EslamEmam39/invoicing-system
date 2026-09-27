<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue'

const props = withDefaults(defineProps<{ message: string; variant?: 'error' | 'success'; duration?: number }>(), { variant: 'error', duration: 5000 })
const emit = defineEmits<{ close: [] }>()
let timeoutId: number | undefined

onMounted(() => {
  timeoutId = window.setTimeout(() => emit('close'), props.duration)
})

onBeforeUnmount(() => {
  if (timeoutId !== undefined) {
    window.clearTimeout(timeoutId)
  }
})
</script>

<template>
  <p class="notice" :class="variant" role="alert">
    <span>{{ message }}</span>
    <button type="button" aria-label="Dismiss error" @click="$emit('close')">×</button>
  </p>
</template>

<style scoped>
.notice { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.notice button { color: inherit; font-size: 1.25rem; line-height: 1; }
</style>
