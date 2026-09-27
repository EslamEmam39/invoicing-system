<script setup lang="ts">
withDefaults(defineProps<{
    variant?: 'primary' | 'danger' | 'text'; type?: 'button' | 'submit' | 'reset';
    disabled?: boolean
}>(),
    { variant: 'primary', type: 'button', disabled: false }
)
</script>
<template><button :class="variant === 'text' ? 'text-button' : variant" :type="type" :disabled="disabled"
        v-bind="$attrs">
        <slot />
    </button></template>
