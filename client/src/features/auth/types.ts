export type UserRole = 'candidate' | 'employer' | 'admin'

export interface AuthUser {
  id: number | string
  name: string
  email: string
  role?: UserRole
}

export interface AuthContext {
  user: AuthUser
  role: UserRole
  abilities: string[]
  profile?: unknown
}

export interface AuthResourceResponse {
  data: AuthContext
}

export interface LoginPayload {
  email: string
  password: string
}

export interface RegisterPayload {
  role: Exclude<UserRole, 'admin'>
  name: string
  email: string
  password: string
  password_confirmation: string
  company_name?: string
}
