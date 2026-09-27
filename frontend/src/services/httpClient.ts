import type { ApiResponse, Paginated } from '../types/api'

const baseUrl = (import.meta.env.VITE_API_URL ?? '/api').replace(/\/$/, '')

async function request<T>(path: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
  const token = localStorage.getItem('invoicing_token')
  const response = await fetch(`${baseUrl}${path}`,
    {
      ...options, headers: {
        Accept: 'application/json', ...(options.body
          ? { 'Content-Type': 'application/json' }
          : {}),
        ...(token
          ? { Authorization: `Bearer ${token}` }
          : {}), ...options.headers
      }
    })
  const body = await response.json().catch(() => null) as ApiResponse<T> | null
  if (!response.ok || !body?.success)
    throw new Error(body?.errors
      ? Object.values(body.errors).flat().join(' ')
      : body?.message || 'The server could not complete the request.')
  return body
}

export async function http<T>(path: string, options: RequestInit = {}): Promise<T> {
  return (await request<T>(path, options)).data
}

export async function httpPaginated<T>(path: string): Promise<Paginated<T>> {
  const body = await request<T[]>(path)
  return { data: body.data, meta: body.meta, links: body.links }
}

export const json = (method: string, body?: unknown): RequestInit =>
({
  method, ...(body === undefined
    ? {}
    : { body: JSON.stringify(body) })
})
