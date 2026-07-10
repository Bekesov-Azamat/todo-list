export default defineNuxtConfig({

  modules: [
    '@pinia/nuxt',
    '@nuxt/eslint',
  ],

  ssr: false,

  devtools: {
    enabled: true,
  },

  app: {
    head: {
      title: 'To-Do List',
      meta: [
        {
          name: 'description',
          content: 'Task management application built with Laravel and Nuxt.',
        },
      ],
    },
  },

  css: [
    '~/assets/css/main.css',
  ],

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8010',
    },
  },
  compatibilityDate: '2025-07-15',

  typescript: {
    strict: true,
  },

  eslint: {
    config: {
      stylistic: true,
    },
  },
})
