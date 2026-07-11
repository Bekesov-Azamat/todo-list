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

export type TaskStatus
  = | 'pending'
    | 'in_progress'
    | 'completed'

export type TaskFilterStatus = 'all' | TaskStatus

export type TaskSort
  = | 'newest'
    | 'oldest'
    | 'due_date_asc'
    | 'due_date_desc'
    | 'status_asc'
    | 'status_desc'
    | 'title_asc'
    | 'title_desc'

export interface Task {
  id: number
  title: string
  description: string | null
  due_date: string | null
  status: TaskStatus
  created_at: string | null
  updated_at: string | null
}

export interface TaskFormPayload {
  title: string
  description: string | null
  due_date: string | null
  status: TaskStatus
}

export interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export interface PaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  path: string
  per_page: number
  to: number | null
  total: number
}

export interface PaginatedResponse<T> {
  data: T[]
  links: PaginationLinks
  meta: PaginationMeta
}

export interface ValidationErrorResponse {
  message: string
  errors?: Record<string, string[]>
}
