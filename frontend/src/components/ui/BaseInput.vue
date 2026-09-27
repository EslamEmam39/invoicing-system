<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: string | number; label: string; type?: string; min?: string | number; step?:
    string; required?: boolean
}>(),
    { type: 'text', required: false })
defineEmits<{ 'update:modelValue': [value: string] }>()
</script>
<template><label>{{ label }}<input :type="type" :min="min" :step="step" :required="required" :value="modelValue"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"></label></template>
