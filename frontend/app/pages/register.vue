<script setup lang="ts">
import type { RegisterPayload } from '~/types/api'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '~/utils/api-error'

definePageMeta({
  middleware: 'guest',
})

useHead({
  title: 'Create account · To-Do List',
})

const auth = useAuthStore()

const form = reactive<RegisterPayload>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const submitting = ref(false)

function fieldError(field: keyof RegisterPayload): string | undefined {
  return errors.value[field]?.[0]
}

async function submit(): Promise<void> {
  errors.value = {}
  errorMessage.value = ''
  submitting.value = true

  try {
    await auth.register(form)
    await navigateTo('/dashboard')
  }
  catch (error: unknown) {
    errors.value = getValidationErrors(error)
    errorMessage.value = getApiErrorMessage(
      error,
      'Unable to create your account. Please try again.',
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
          Get started
        </p>

        <h1>Create your account</h1>

        <p>
          Build a clear, private list of tasks that belongs only to you.
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
          <label for="register-name">
            Name
          </label>

          <input
            id="register-name"
            v-model.trim="form.name"
            name="name"
            type="text"
            autocomplete="name"
            placeholder="Your name"
            required
            :aria-invalid="Boolean(fieldError('name'))"
            :aria-describedby="fieldError('name') ? 'register-name-error' : undefined"
          >

          <p
            v-if="fieldError('name')"
            id="register-name-error"
            class="field-error"
          >
            {{ fieldError('name') }}
          </p>
        </div>

        <div class="form-field">
          <label for="register-email">
            Email
          </label>

          <input
            id="register-email"
            v-model.trim="form.email"
            name="email"
            type="email"
            autocomplete="email"
            placeholder="you@example.com"
            required
            :aria-invalid="Boolean(fieldError('email'))"
            :aria-describedby="fieldError('email') ? 'register-email-error' : undefined"
          >

          <p
            v-if="fieldError('email')"
            id="register-email-error"
            class="field-error"
          >
            {{ fieldError('email') }}
          </p>
        </div>

        <div class="form-field">
          <label for="register-password">
            Password
          </label>

          <input
            id="register-password"
            v-model="form.password"
            name="password"
            type="password"
            autocomplete="new-password"
            placeholder="Create a strong password"
            required
            :aria-invalid="Boolean(fieldError('password'))"
            :aria-describedby="fieldError('password') ? 'register-password-error' : 'password-hint'"
          >

          <p
            id="password-hint"
            class="field-hint"
          >
            At least 8 characters with uppercase, lowercase and a number.
          </p>

          <p
            v-if="fieldError('password')"
            id="register-password-error"
            class="field-error"
          >
            {{ fieldError('password') }}
          </p>
        </div>

        <div class="form-field">
          <label for="register-password-confirmation">
            Confirm password
          </label>

          <input
            id="register-password-confirmation"
            v-model="form.password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            placeholder="Repeat your password"
            required
          >
        </div>

        <button
          class="button button--primary button--full"
          type="submit"
          :disabled="submitting"
        >
          {{ submitting ? 'Creating account…' : 'Create account' }}
        </button>
      </form>

      <p class="auth-switch">
        Already have an account?

        <NuxtLink to="/login">
          Sign in
        </NuxtLink>
      </p>
    </section>
  </main>
</template>
