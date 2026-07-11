<script setup lang="ts">
import type {
  TaskFilterStatus,
  TaskSort,
} from '~/types/api'

const props = defineProps<{
  search: string
  status: TaskFilterStatus
  sort: TaskSort
  loading: boolean
}>()

const emit = defineEmits<{
  'update:search': [value: string]
  'update:status': [value: TaskFilterStatus]
  'update:sort': [value: TaskSort]
  'reset': []
}>()

const hasActiveFilters = computed(() => {
  return props.search.trim() !== ''
    || props.status !== 'all'
    || props.sort !== 'newest'
})

function updateSearch(event: Event): void {
  const target = event.target

  if (!(target instanceof HTMLInputElement)) {
    return
  }

  emit('update:search', target.value)
}

function updateStatus(event: Event): void {
  const target = event.target

  if (!(target instanceof HTMLSelectElement)) {
    return
  }

  emit(
    'update:status',
    target.value as TaskFilterStatus,
  )
}

function updateSort(event: Event): void {
  const target = event.target

  if (!(target instanceof HTMLSelectElement)) {
    return
  }

  emit(
    'update:sort',
    target.value as TaskSort,
  )
}
</script>

<template>
  <section
    class="task-filters"
    aria-label="Task filters"
    :aria-busy="loading"
  >
    <div class="task-filter task-filter--search">
      <label for="task-search">
        Search
      </label>

      <input
        id="task-search"
        name="search"
        type="search"
        autocomplete="off"
        placeholder="Search title or description"
        :value="search"
        @input="updateSearch"
      >
    </div>

    <div class="task-filter">
      <label for="task-filter-status">
        Status
      </label>

      <select
        id="task-filter-status"
        name="filter_status"
        :value="status"
        @change="updateStatus"
      >
        <option value="all">
          All statuses
        </option>

        <option value="pending">
          Pending
        </option>

        <option value="in_progress">
          In progress
        </option>

        <option value="completed">
          Completed
        </option>
      </select>
    </div>

    <div class="task-filter">
      <label for="task-sort">
        Sort
      </label>

      <select
        id="task-sort"
        name="sort"
        :value="sort"
        @change="updateSort"
      >
        <option value="newest">
          Newest first
        </option>

        <option value="oldest">
          Oldest first
        </option>

        <option value="due_date_asc">
          Nearest deadline
        </option>

        <option value="due_date_desc">
          Latest deadline
        </option>

        <option value="status_asc">
          Pending first
        </option>

        <option value="status_desc">
          Completed first
        </option>

        <option value="title_asc">
          Title A–Z
        </option>

        <option value="title_desc">
          Title Z–A
        </option>
      </select>
    </div>

    <button
      class="task-filters__reset"
      type="button"
      :disabled="!hasActiveFilters"
      @click="emit('reset')"
    >
      Clear filters
    </button>
  </section>
</template>
