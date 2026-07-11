import { ofetch } from 'ofetch'
import type { Pinia } from 'pinia'

const unsafeMethods = new Set([
  'POST',
  'PUT',
  'PATCH',
  'DELETE',
])

const ignoredUnauthorizedPaths = new Set([
  '/api/user',
  '/api/auth/login',
  '/api/auth/register',
  '/sanctum/csrf-cookie',
])

function readCookie(name: string): string | null {
  if (!import.meta.client) {
    return null
  }

  const prefix = encodeURIComponent(name) + '='

  const cookie = document.cookie
    .split('; ')
    .find(entry => entry.startsWith(prefix))

  if (!cookie) {
    return null
  }

  return decodeURIComponent(
    cookie.slice(prefix.length),
  )
}

function requestPath(request: unknown): string {
  const rawRequest = request instanceof Request
    ? request.url
    : String(request)

  try {
    return new URL(
      rawRequest,
      'http://localhost',
    ).pathname
  }
  catch {
    return rawRequest.split('?')[0] ?? rawRequest
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  const router = useRouter()

  let redirectingToLogin = false

  const api = ofetch.create({
    baseURL: config.public.apiBase,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
    retry: 0,
    timeout: 10_000,

    onRequest({ options }) {
      const method = String(
        options.method ?? 'GET',
      ).toUpperCase()

      if (!unsafeMethods.has(method)) {
        return
      }

      const token = readCookie('XSRF-TOKEN')

      if (!token) {
        return
      }

      const headers = new Headers(options.headers)

      headers.set('X-XSRF-TOKEN', token)

      options.headers = headers
    },

    async onResponseError({
      request,
      response,
    }) {
      if (
        !import.meta.client
        || response.status !== 401
      ) {
        return
      }

      const path = requestPath(request)

      if (ignoredUnauthorizedPaths.has(path)) {
        return
      }

      const auth = useAuthStore(
        nuxtApp.$pinia as Pinia,
      )

      auth.clearSession()

      if (
        redirectingToLogin
        || router.currentRoute.value.path === '/login'
      ) {
        return
      }

      redirectingToLogin = true

      try {
        await router.replace({
          path: '/login',
          query: {
            redirect: router.currentRoute.value.fullPath,
          },
        })
      }
      finally {
        redirectingToLogin = false
      }
    },
  })

  return {
    provide: {
      api,
    },
  }
})
