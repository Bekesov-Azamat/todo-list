const unsafeMethods = new Set([
  'POST',
  'PUT',
  'PATCH',
  'DELETE',
])

function readBrowserCookie(name: string): string | null {
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

  return decodeURIComponent(cookie.slice(prefix.length))
}

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    credentials: 'include',
    retry: 0,
    timeout: 10_000,

    onRequest({ options }) {
      const method = String(options.method ?? 'GET').toUpperCase()
      const headers = new Headers(options.headers)

      headers.set('Accept', 'application/json')

      if (unsafeMethods.has(method)) {
        const csrfToken = readBrowserCookie('XSRF-TOKEN')

        if (csrfToken) {
          headers.set('X-XSRF-TOKEN', csrfToken)
        }
      }

      options.headers = headers
    },
  })

  return {
    provide: {
      api,
    },
  }
})
