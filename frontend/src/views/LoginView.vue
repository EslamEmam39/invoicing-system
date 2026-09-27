<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuth } from '../composables/useAuth'
import { useRouter } from '../router'
const { login } = useAuth()
const { push } = useRouter()
const form = reactive({ email: '', password: '' })
const error = ref('')

async function submit() {
  try {
    await login(form)
    push('/')
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Unable to sign in.'
  }
}
</script>
<template>
  <main class="login-shell">
    <section class="login-card">
      <p class="eyebrow">INVOICE FLOW</p>
      <h1>Run your sales with clarity.</h1>
      <p v-if="error" class="notice error">{{ error }}</p>
      <form @submit.prevent="submit"><label>Email<input v-model="form.email" required
            type="email"></label><label>Password<input v-model="form.password" required type="password"></label>
        <button class="primary wide">Sign in</button>
      </form>
    </section>
  </main>
</template>
