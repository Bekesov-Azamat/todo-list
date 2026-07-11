import type { FetchError } from 'ofetch'
import type { ValidationErrorResponse } from '~/types/api'

export function getApiResponseStatus(error: unknown): number | undefined {
  return (error as FetchError<ValidationErrorResponse>).response?.status
}

export function getValidationErrors(
  error: unknown,
): Record<string, string[]> {
  return (error as FetchError<ValidationErrorResponse>).data?.errors ?? {}
}

export function getApiErrorMessage(
  error: unknown,
  fallback: string,
): string {
  return (error as FetchError<ValidationErrorResponse>).data?.message
    ?? fallback
}
