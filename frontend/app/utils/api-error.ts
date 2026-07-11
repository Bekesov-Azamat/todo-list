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

export function getApiStatus(
  error: unknown,
): number | undefined {
  if (
    typeof error !== 'object'
    || error === null
    || !('response' in error)
  ) {
    return undefined
  }

  const response = error.response

  if (
    typeof response !== 'object'
    || response === null
    || !('status' in response)
  ) {
    return undefined
  }

  return typeof response.status === 'number'
    ? response.status
    : undefined
}
