export interface ApiResource<T> {
  data: T
}

export interface AuthUser {
  id: number
  name: string
  email: string
  created_at: string | null
}

export interface LoginPayload {
  email: string
  password: string
  remember: boolean
}

export interface RegisterPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface ValidationErrorResponse {
  message: string
  errors?: Record<string, string[]>
}
