export type PaginationMeta = { current_page: number; last_page: number; total: number }
export type PaginationLinks = { first: string | null; last: string | null; prev: string | null; next: string | null }
export type ApiResponse<T> = { success: boolean; message: string; data: T; errors?: Record<string, string[]>; meta?: PaginationMeta; links?: PaginationLinks }
export type Paginated<T> = { data: T[]; meta?: PaginationMeta; links?: PaginationLinks }
