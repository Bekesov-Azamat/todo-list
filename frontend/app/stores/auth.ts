import type {
  ApiResource,
  AuthUser,
  LoginPayload,
  RegisterPayload,
} from '~/types/api'
import { getApiResponseStatus } from '~/utils/api-error'

export const useAuthStore = defineStore('auth', () => {
  const { $api } = useNuxtApp()

  const user = ref<AuthUser | null>(null)
  const initialized = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  async function csrf(): Promise<void> {
    await $api('/sanctum/csrf-cookie')
  }

  async function fetchUser(): Promise<void> {
    try {
      const response = await $api<ApiResource<AuthUser>>('/api/user')

      user.value = response.data
      initialized.value = true
    }
    catch (error: unknown) {
      if (getApiResponseStatus(error) === 401) {
        user.value = null
        initialized.value = true

        return
      }

      throw error
    }
  }

  async function initialize(): Promise<void> {
    if (initialized.value) {
      return
    }

    await fetchUser()
  }

  async function login(payload: LoginPayload): Promise<void> {
    await csrf()

    const response = await $api<ApiResource<AuthUser>>('/api/auth/login', {
      method: 'POST',
      body: payload,
    })

    user.value = response.data
    initialized.value = true
  }

  async function register(payload: RegisterPayload): Promise<void> {
    await csrf()

    const response = await $api<ApiResource<AuthUser>>(
      '/api/auth/register',
      {
        method: 'POST',
        body: payload,
      },
    )

    user.value = response.data
    initialized.value = true
  }

  async function logout(): Promise<void> {
    await csrf()

    await $api('/api/auth/logout', {
      method: 'POST',
    })

    user.value = null
    initialized.value = true
  }

  return {
    user,
    initialized,
    isAuthenticated,
    initialize,
    login,
    register,
    logout,
  }
})
