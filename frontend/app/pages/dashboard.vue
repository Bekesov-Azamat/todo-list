<script setup lang="ts">
import TaskCard from '~/components/tasks/TaskCard.vue'
import TaskForm from '~/components/tasks/TaskForm.vue'
import type {
  ApiResource,
  PaginatedResponse,
  Task,
  TaskFormPayload,
} from '~/types/api'
import {
  getApiErrorMessage,
  getValidationErrors,
} from '~/utils/api-error'

definePageMeta({
  middleware: 'auth',
})

useHead({
  title: 'Tasks · To-Do List',
})

const auth = useAuthStore()
const { $api } = useNuxtApp()

const tasks = ref<Task[]>([])
const loading = ref(true)
const pageError = ref('')

const formOpen = ref(false)
const editingTask = ref<Task | null>(null)
const submitting = ref(false)
const formErrors = ref<Record<string, string[]>>({})
const formErrorMessage = ref('')

const deletingTaskId = ref<number | null>(null)
const loggingOut = ref(false)

const taskCountLabel = computed(() => {
  const count = tasks.value.length

  return count === 1
    ? '1 task'
    : `${count} tasks`
})

async function csrf(): Promise<void> {
  await $api('/sanctum/csrf-cookie')
}

async function fetchTasks(): Promise<void> {
  loading.value = true
  pageError.value = ''

  try {
    const response = await $api<
      PaginatedResponse<Task>
    >('/api/tasks', {
      query: {
        per_page: 50,
        sort: 'newest',
      },
    })

    tasks.value = response.data
  }
  catch (error: unknown) {
    pageError.value = getApiErrorMessage(
      error,
      'Unable to load your tasks. Please try again.',
    )
  }
  finally {
    loading.value = false
  }
}

function openCreateForm(): void {
  editingTask.value = null
  formErrors.value = {}
  formErrorMessage.value = ''
  formOpen.value = true
}

function openEditForm(task: Task): void {
  editingTask.value = task
  formErrors.value = {}
  formErrorMessage.value = ''
  formOpen.value = true
}

function resetForm(): void {
  formOpen.value = false
  editingTask.value = null
  formErrors.value = {}
  formErrorMessage.value = ''
}

function closeForm(): void {
  if (submitting.value) {
    return
  }

  resetForm()
}

async function saveTask(
  payload: TaskFormPayload,
): Promise<void> {
  formErrors.value = {}
  formErrorMessage.value = ''
  submitting.value = true

  try {
    await csrf()

    const currentTask = editingTask.value

    const response = currentTask
      ? await $api<ApiResource<Task>>(
          `/api/tasks/${currentTask.id}`,
          {
            method: 'PATCH',
            body: payload,
          },
        )
      : await $api<ApiResource<Task>>('/api/tasks', {
          method: 'POST',
          body: payload,
        })

    if (currentTask) {
      const index = tasks.value.findIndex(
        task => task.id === response.data.id,
      )

      if (index !== -1) {
        tasks.value[index] = response.data
      }
    }
    else {
      tasks.value.unshift(response.data)
    }

    resetForm()
  }
  catch (error: unknown) {
    formErrors.value = getValidationErrors(error)
    formErrorMessage.value = getApiErrorMessage(
      error,
      'Unable to save the task. Please try again.',
    )
  }
  finally {
    submitting.value = false
  }
}

async function deleteTask(task: Task): Promise<void> {
  const confirmed = window.confirm(
    `Delete "${task.title}"? This action cannot be undone.`,
  )

  if (!confirmed) {
    return
  }

  pageError.value = ''
  deletingTaskId.value = task.id

  try {
    await csrf()

    await $api(`/api/tasks/${task.id}`, {
      method: 'DELETE',
    })

    tasks.value = tasks.value.filter(
      currentTask => currentTask.id !== task.id,
    )
  }
  catch (error: unknown) {
    pageError.value = getApiErrorMessage(
      error,
      'Unable to delete the task. Please try again.',
    )
  }
  finally {
    deletingTaskId.value = null
  }
}

async function logout(): Promise<void> {
  pageError.value = ''
  loggingOut.value = true

  try {
    await auth.logout()
    await navigateTo('/login')
  }
  catch (error: unknown) {
    pageError.value = getApiErrorMessage(
      error,
      'Unable to sign out. Please try again.',
    )
  }
  finally {
    loggingOut.value = false
  }
}

onMounted(() => {
  void fetchTasks()
})
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

      <div class="dashboard-header__actions">
        <span class="dashboard-user">
          {{ auth.user?.name }}
        </span>

        <button
          class="button button--secondary"
          type="button"
          :disabled="loggingOut"
          @click="logout"
        >
          {{ loggingOut ? 'Signing out…' : 'Sign out' }}
        </button>
      </div>
    </header>

    <section class="dashboard-content">
      <div class="dashboard-toolbar">
        <div>
          <p class="eyebrow">
            Private workspace
          </p>

          <h1>Your tasks</h1>

          <p>
            Keep work visible, focused and under control.
          </p>
        </div>

        <button
          class="button button--primary"
          type="button"
          @click="openCreateForm"
        >
          New task
        </button>
      </div>

      <p
        v-if="pageError"
        class="alert alert--error dashboard-alert"
        role="alert"
      >
        {{ pageError }}

        <button
          v-if="!loading"
          class="alert__action"
          type="button"
          @click="fetchTasks"
        >
          Try again
        </button>
      </p>

      <div class="task-list-heading">
        <h2>Task list</h2>
        <span>{{ taskCountLabel }}</span>
      </div>

      <div
        v-if="loading"
        class="task-grid"
        aria-label="Loading tasks"
        aria-busy="true"
      >
        <div
          v-for="index in 3"
          :key="index"
          class="task-skeleton"
        >
          <span />
          <span />
          <span />
        </div>
      </div>

      <section
        v-else-if="tasks.length === 0 && !pageError"
        class="task-empty-state"
      >
        <div class="task-empty-state__icon">
          ✓
        </div>

        <h2>No tasks yet</h2>

        <p>
          Create your first task and start building a clear
          workspace.
        </p>

        <button
          class="button button--primary"
          type="button"
          @click="openCreateForm"
        >
          Create first task
        </button>
      </section>

      <div
        v-else-if="tasks.length > 0"
        class="task-grid"
      >
        <TaskCard
          v-for="task in tasks"
          :key="task.id"
          :task="task"
          :deleting="deletingTaskId === task.id"
          @edit="openEditForm"
          @remove="deleteTask"
        />
      </div>
    </section>

    <div
      v-if="formOpen"
      class="modal-backdrop"
      @click.self="closeForm"
    >
      <section
        class="task-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="task-form-title"
      >
        <header class="task-modal__header">
          <div>
            <p class="eyebrow">
              {{ editingTask ? 'Update task' : 'New task' }}
            </p>

            <h2 id="task-form-title">
              {{
                editingTask
                  ? 'Edit task'
                  : 'Create a task'
              }}
            </h2>
          </div>

          <button
            class="modal-close"
            type="button"
            aria-label="Close task form"
            :disabled="submitting"
            @click="closeForm"
          >
            ×
          </button>
        </header>

        <TaskForm
          :task="editingTask"
          :submitting="submitting"
          :server-errors="formErrors"
          :general-error="formErrorMessage"
          @submit="saveTask"
          @cancel="closeForm"
        />
      </section>
    </div>
  </main>
</template>
