<script setup lang="ts">
import { getApiErrorMessage } from '~/utils/api-error'

definePageMeta({
  middleware: 'auth',
})

useHead({
  title: 'Dashboard · To-Do List',
})

const auth = useAuthStore()

const loggingOut = ref(false)
const errorMessage = ref('')

async function logout(): Promise<void> {
  errorMessage.value = ''
  loggingOut.value = true

  try {
    await auth.logout()
    await navigateTo('/login')
  }
  catch (error: unknown) {
    errorMessage.value = getApiErrorMessage(
      error,
      'Unable to sign out. Please try again.',
    )
  }
  finally {
    loggingOut.value = false
  }
}
</script>

<template>
  <main class="dashboard-page">
    <header class="dashboard-header">
      <NuxtLink
        class="brand-link"
        to="/"
      >
        To-Do List
      </NuxtLink>

      <button
        class="button button--secondary"
        type="button"
        :disabled="loggingOut"
        @click="logout"
      >
        {{ loggingOut ? 'Signing out…' : 'Sign out' }}
      </button>
    </header>

    <section class="dashboard-content">
      <p
        v-if="errorMessage"
        class="alert alert--error"
        role="alert"
      >
        {{ errorMessage }}
      </p>

      <article class="dashboard-card">
        <p class="eyebrow">
          Private workspace
        </p>

        <h1>
          Welcome, {{ auth.user?.name }}
        </h1>

        <p class="dashboard-card__description">
          Your session is active and the protected dashboard is available.
        </p>

        <dl class="account-details">
          <div>
            <dt>Name</dt>
            <dd>{{ auth.user?.name }}</dd>
          </div>

          <div>
            <dt>Email</dt>
            <dd>{{ auth.user?.email }}</dd>
          </div>
        </dl>

        <div class="empty-state">
          <h2>Your task workspace is ready</h2>

          <p>
            Task creation and management will be connected in the next
            application sprint.
          </p>
        </div>
      </article>
    </section>
  </main>
</template>
