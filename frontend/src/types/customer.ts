export type Customer = { id: number; name: string; phone: string | null; address: string | null; is_active: boolean }
export type CustomerPayload = Omit<Customer, 'id'>
