import type { LoginCredentials, User } from '../types/auth'
import { http, json } from './httpClient'
type Session = { token: string; token_type: string; user: User }
export const authService =
{
    login: (body: LoginCredentials) =>
        http<Session>('/auth/login', json('POST', body)), logout: () =>
            http<null>('/auth/logout', json('POST')), currentUser: () =>
                http<User>('/auth/user')
}
