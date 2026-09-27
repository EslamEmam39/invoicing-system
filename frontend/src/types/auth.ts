export type Role = 'admin' | 'employee' | null
export type User = { id: number; name: string; email: string; role: Role }
export type LoginCredentials = { email: string; password: string }
