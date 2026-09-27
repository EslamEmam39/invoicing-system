import { ref } from 'vue'
export type RouteName = 'dashboard' | 'products' | 'customers' | 'invoices' | 'returns' | 'invoice-detail' | 'not-found'
const routes: Record<string, RouteName> = {
    '/': 'dashboard', '/products': 'products', '/customers': 'customers', '/invoices': 'invoices', '/returns': 'returns'
}
const route = ref<RouteName>('dashboard')
const invoiceId = ref<number | null>(null)
function resolve(path = window.location.pathname) {
    const detail = path.match(/^\/invoices\/(\d+)$/);
    invoiceId.value = detail ? Number(detail[1]) : null; route.value = detail
        ? 'invoice-detail' : routes[path] ?? 'not-found'
}
export function useRouter() {
    function push(path: string) { window.history.pushState({}, '', path); resolve(path) };
    return { route, invoiceId, push }
}
window.addEventListener('popstate', () => resolve())
resolve()
