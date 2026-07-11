<script setup lang="ts">
useHead({
  title: 'To-Do List',
})
</script>

<template>
  <main class="landing-page">
    <section class="landing-card">
      <p class="eyebrow">
        Laravel 13 + Nuxt 4
      </p>

      <h1>To-Do List</h1>

      <p class="landing-card__description">
        A focused and private workspace for organizing tasks without
        unnecessary complexity.
      </p>

      <div class="landing-actions">
        <NuxtLink
          class="button button--primary"
          to="/register"
        >
          Create account
        </NuxtLink>

        <NuxtLink
          class="button button--secondary"
          to="/login"
        >
          Sign in
        </NuxtLink>
      </div>
    </section>
  </main>
</template>
