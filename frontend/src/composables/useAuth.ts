import { computed, ref } from 'vue'
import { authService } from '../services/authService'
import type { LoginCredentials, User } from '../types/auth'
const user = ref<User | null>(JSON.parse(localStorage.getItem('invoicing_user') ?? 'null'))
const token = ref(localStorage.getItem('invoicing_token') ?? '')
export function useAuth() {
  const isAuthenticated = computed(() => Boolean(token.value))
  const isAdmin = computed(() => user.value?.role === 'admin')
  async function login(credentials: LoginCredentials) {
    const session = await authService.login(credentials)
    token.value = session.token
    user.value = session.user
    localStorage.setItem('invoicing_token', session.token)
    localStorage.setItem('invoicing_user', JSON.stringify(session.user))
  }
  async function logout() {
    try { await authService.logout() } finally {
      token.value = ''
      user.value = null
      localStorage.removeItem('invoicing_token')
      localStorage.removeItem('invoicing_user')
    }
  }
  return { user, token, isAuthenticated, isAdmin, login, logout }
}
