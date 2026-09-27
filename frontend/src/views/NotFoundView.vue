<script setup lang="ts">
import { useRouter } from '../router'

const { push } = useRouter()
</script>

<template>
  <section class="empty-page">
    <h1>Page not found</h1>
    <button class="primary" @click="push('/')">Go to dashboard</button>
  </section>
</template>
