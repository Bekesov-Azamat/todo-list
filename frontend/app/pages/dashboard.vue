<script setup lang="ts">
import TaskCard from '~/components/tasks/TaskCard.vue'
import TaskFilters from '~/components/tasks/TaskFilters.vue'
import TaskForm from '~/components/tasks/TaskForm.vue'
import TaskPagination from '~/components/tasks/TaskPagination.vue'
import type {
  ApiResource,
  PaginatedResponse,
  PaginationMeta,
  Task,
  TaskFilterStatus,
  TaskFormPayload,
  TaskSort,
} from '~/types/api'
import {
  getApiErrorMessage,
  getApiStatus,
  getValidationErrors,
} from '~/utils/api-error'

interface TaskQueryOverrides {
  search?: string
  status?: TaskFilterStatus
  sort?: TaskSort
  page?: number
}

definePageMeta({
  middleware: 'auth',
})

useHead({
  title: 'Tasks · To-Do List',
})

const auth = useAuthStore()
const isAdmin = computed(
  () => auth.user?.role === 'admin',
)

const route = useRoute()
const router = useRouter()
const { $api } = useNuxtApp()

const validStatuses = new Set<TaskFilterStatus>([
  'all',
  'pending',
  'in_progress',
  'completed',
])

const validSorts = new Set<TaskSort>([
  'newest',
  'oldest',
  'due_date_asc',
  'due_date_desc',
  'status_asc',
  'status_desc',
  'title_asc',
  'title_desc',
])

const tasks = ref<Task[]>([])
const loading = ref(true)
const pageError = ref('')

const searchInput = ref('')
const status = ref<TaskFilterStatus>('all')
const sort = ref<TaskSort>('newest')
const currentPage = ref(1)

const pagination = ref<PaginationMeta>({
  current_page: 1,
  from: null,
  last_page: 1,
  path: '/api/tasks',
  per_page: 6,
  to: null,
  total: 0,
})

const formOpen = ref(false)
const editingTask = ref<Task | null>(null)
const submitting = ref(false)
const formErrors = ref<Record<string, string[]>>({})
const formErrorMessage = ref('')

const deletingTaskId = ref<number | null>(null)
const loggingOut = ref(false)

let searchTimer: ReturnType<typeof setTimeout> | null = null
let latestRequestId = 0

const hasActiveFilters = computed(() => {
  return searchInput.value.trim() !== ''
    || status.value !== 'all'
    || sort.value !== 'newest'
})

const taskCountLabel = computed(() => {
  const count = pagination.value.total

  return count === 1
    ? '1 task'
    : `${count} tasks`
})

const taskRangeLabel = computed(() => {
  if (
    pagination.value.total === 0
    || pagination.value.from === null
    || pagination.value.to === null
  ) {
    return taskCountLabel.value
  }

  return `${pagination.value.from}–${pagination.value.to}`
    + ` of ${pagination.value.total}`
})

function queryString(value: unknown): string {
  if (Array.isArray(value)) {
    return queryString(value[0])
  }

  return typeof value === 'string'
    ? value
    : ''
}

function queryPage(value: unknown): number {
  const parsed = Number.parseInt(
    queryString(value),
    10,
  )

  return Number.isInteger(parsed) && parsed > 0
    ? parsed
    : 1
}

function queryStatus(value: unknown): TaskFilterStatus {
  const parsed = queryString(value) as TaskFilterStatus

  return validStatuses.has(parsed)
    ? parsed
    : 'all'
}

function querySort(value: unknown): TaskSort {
  const parsed = queryString(value) as TaskSort

  return validSorts.has(parsed)
    ? parsed
    : 'newest'
}

function buildRouteQuery(
  overrides: TaskQueryOverrides = {},
): Record<string, string> {
  const nextSearch = (
    overrides.search ?? searchInput.value
  ).trim()

  const nextStatus = overrides.status ?? status.value
  const nextSort = overrides.sort ?? sort.value
  const nextPage = overrides.page ?? currentPage.value

  const query: Record<string, string> = {}

  if (nextSearch !== '') {
    query.search = nextSearch
  }

  if (nextStatus !== 'all') {
    query.status = nextStatus
  }

  if (nextSort !== 'newest') {
    query.sort = nextSort
  }

  if (nextPage > 1) {
    query.page = String(nextPage)
  }

  return query
}

async function updateRoute(
  overrides: TaskQueryOverrides = {},
): Promise<void> {
  await router.replace({
    query: buildRouteQuery(overrides),
  })
}

async function csrf(): Promise<void> {
  await $api('/sanctum/csrf-cookie')
}

async function fetchTasks(): Promise<void> {
  const requestId = ++latestRequestId

  loading.value = true
  pageError.value = ''

  const query: Record<string, string | number> = {
    page: currentPage.value,
    per_page: 6,
    status: status.value,
    sort: sort.value,
  }

  const search = searchInput.value.trim()

  if (search !== '') {
    query.search = search
  }

  try {
    const response = await $api<
      PaginatedResponse<Task>
    >('/api/tasks', {
      query,
    })

    if (requestId !== latestRequestId) {
      return
    }

    if (
      response.meta.current_page
      > response.meta.last_page
    ) {
      await updateRoute({
        page: response.meta.last_page,
      })

      return
    }

    tasks.value = response.data
    pagination.value = response.meta
  }
  catch (error: unknown) {
    if (
      requestId !== latestRequestId
      || getApiStatus(error) === 401
    ) {
      return
    }

    pageError.value = getApiErrorMessage(
      error,
      'Unable to load your tasks. Please try again.',
    )
  }
  finally {
    if (requestId === latestRequestId) {
      loading.value = false
    }
  }
}

function updateSearch(value: string): void {
  searchInput.value = value

  if (searchTimer !== null) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(() => {
    void updateRoute({
      search: value,
      page: 1,
    })
  }, 400)
}

function updateStatus(value: TaskFilterStatus): void {
  status.value = value

  void updateRoute({
    status: value,
    page: 1,
  })
}

function updateSort(value: TaskSort): void {
  sort.value = value

  void updateRoute({
    sort: value,
    page: 1,
  })
}

function resetFilters(): void {
  if (searchTimer !== null) {
    clearTimeout(searchTimer)
    searchTimer = null
  }

  searchInput.value = ''
  status.value = 'all'
  sort.value = 'newest'
  currentPage.value = 1

  void router.replace({
    query: {},
  })
}

function changePage(page: number): void {
  void updateRoute({
    page,
  })
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

  const creatingTask = editingTask.value === null

  try {
    await csrf()

    const currentTask = editingTask.value

    if (currentTask) {
      await $api<ApiResource<Task>>(
        `/api/tasks/${currentTask.id}`,
        {
          method: 'PATCH',
          body: payload,
        },
      )
    }
    else {
      await $api<ApiResource<Task>>('/api/tasks', {
        method: 'POST',
        body: payload,
      })
    }

    resetForm()

    if (
      creatingTask
      && currentPage.value !== 1
    ) {
      await updateRoute({
        page: 1,
      })
    }
    else {
      await fetchTasks()
    }
  }
  catch (error: unknown) {
    if (getApiStatus(error) === 401) {
      return
    }

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

    if (
      tasks.value.length === 1
      && currentPage.value > 1
    ) {
      await updateRoute({
        page: currentPage.value - 1,
      })
    }
    else {
      await fetchTasks()
    }
  }
  catch (error: unknown) {
    if (getApiStatus(error) === 401) {
      return
    }

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
    if (getApiStatus(error) === 401) {
      return
    }

    pageError.value = getApiErrorMessage(
      error,
      'Unable to sign out. Please try again.',
    )
  }
  finally {
    loggingOut.value = false
  }
}

watch(
  () => route.query,
  (query) => {
    searchInput.value = queryString(query.search)
    status.value = queryStatus(query.status)
    sort.value = querySort(query.sort)
    currentPage.value = queryPage(query.page)

    void fetchTasks()
  },
  {
    immediate: true,
  },
)

onBeforeUnmount(() => {
  latestRequestId++

  if (searchTimer !== null) {
    clearTimeout(searchTimer)
  }
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

          <span
            v-if="isAdmin"
            class="dashboard-role-badge"
          >
            Admin
          </span>
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
            {{
              isAdmin
                ? 'Administrator workspace'
                : 'Private workspace'
            }}
          </p>

          <h1>
            {{ isAdmin ? 'All tasks' : 'Your tasks' }}
          </h1>

          <p>
            {{
              isAdmin
                ? 'Review and manage tasks across all users.'
                : 'Keep work visible, focused and under control.'
            }}
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

      <TaskFilters
        :search="searchInput"
        :status="status"
        :sort="sort"
        :loading="loading"
        @update:search="updateSearch"
        @update:status="updateStatus"
        @update:sort="updateSort"
        @reset="resetFilters"
      />

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
        <span>{{ taskRangeLabel }}</span>
      </div>

      <div
        v-if="loading"
        class="task-grid"
        aria-label="Loading tasks"
        aria-busy="true"
      >
        <div
          v-for="index in 6"
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

        <h2>
          {{
            hasActiveFilters
              ? 'No matching tasks'
              : 'No tasks yet'
          }}
        </h2>

        <p>
          {{
            hasActiveFilters
              ? 'Try changing or clearing the current filters.'
              : 'Create your first task and start building a clear workspace.'
          }}
        </p>

        <button
          v-if="hasActiveFilters"
          class="button button--secondary"
          type="button"
          @click="resetFilters"
        >
          Clear filters
        </button>

        <button
          v-else
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
          :show-owner="isAdmin"
          @edit="openEditForm"
          @remove="deleteTask"
        />
      </div>

      <TaskPagination
        :meta="pagination"
        :loading="loading"
        @change="changePage"
      />
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
