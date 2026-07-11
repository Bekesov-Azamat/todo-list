<script setup lang="ts">
import type { LoginPayload } from '~/types/api'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '~/utils/api-error'

definePageMeta({
  middleware: 'guest',
})

useHead({
  title: 'Sign in · To-Do List',
})

const auth = useAuthStore()

const form = reactive<LoginPayload>({
  email: '',
  password: '',
  remember: false,
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const submitting = ref(false)

function fieldError(field: keyof LoginPayload): string | undefined {
  return errors.value[field]?.[0]
}

async function submit(): Promise<void> {
  errors.value = {}
  errorMessage.value = ''
  submitting.value = true

  try {
    await auth.login(form)
    await navigateTo('/dashboard')
  }
  catch (error: unknown) {
    errors.value = getValidationErrors(error)
    errorMessage.value = getApiErrorMessage(
      error,
      'Unable to sign in. Please try again.',
    )
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <NuxtLink
        class="brand-link"
        to="/"
      >
        To-Do List
      </NuxtLink>

      <div class="auth-heading">
        <p class="eyebrow">
          Welcome back
        </p>

        <h1>Sign in to your account</h1>

        <p>
          Continue managing your tasks from one focused workspace.
        </p>
      </div>

      <p
        v-if="errorMessage"
        class="alert alert--error"
        role="alert"
      >
        {{ errorMessage }}
      </p>

      <form
        class="auth-form"
        @submit.prevent="submit"
      >
        <div class="form-field">
          <label for="login-email">
            Email
          </label>

          <input
            id="login-email"
            v-model.trim="form.email"
            name="email"
            type="email"
            autocomplete="email"
            placeholder="you@example.com"
            required
            :aria-invalid="Boolean(fieldError('email'))"
            :aria-describedby="fieldError('email') ? 'login-email-error' : undefined"
          >

          <p
            v-if="fieldError('email')"
            id="login-email-error"
            class="field-error"
          >
            {{ fieldError('email') }}
          </p>
        </div>

        <div class="form-field">
          <label for="login-password">
            Password
          </label>

          <input
            id="login-password"
            v-model="form.password"
            name="password"
            type="password"
            autocomplete="current-password"
            placeholder="Enter your password"
            required
            :aria-invalid="Boolean(fieldError('password'))"
            :aria-describedby="fieldError('password') ? 'login-password-error' : undefined"
          >

          <p
            v-if="fieldError('password')"
            id="login-password-error"
            class="field-error"
          >
            {{ fieldError('password') }}
          </p>
        </div>

        <label class="checkbox-field">
          <input
            v-model="form.remember"
            name="remember"
            type="checkbox"
          >

          <span>Keep me signed in</span>
        </label>

        <button
          class="button button--primary button--full"
          type="submit"
          :disabled="submitting"
        >
          {{ submitting ? 'Signing in…' : 'Sign in' }}
        </button>
      </form>

      <p class="auth-switch">
        New here?

        <NuxtLink to="/register">
          Create an account
        </NuxtLink>
      </p>
    </section>
  </main>
</template>
