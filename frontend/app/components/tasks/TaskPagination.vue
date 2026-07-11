<script setup lang="ts">
import type { PaginationMeta } from '~/types/api'

const props = defineProps<{
  meta: PaginationMeta
  loading: boolean
}>()

const emit = defineEmits<{
  change: [page: number]
}>()

const visiblePages = computed(() => {
  const lastPage = props.meta.last_page

  if (lastPage <= 1) {
    return []
  }

  let start = Math.max(
    1,
    props.meta.current_page - 2,
  )

  let end = Math.min(
    lastPage,
    start + 4,
  )

  start = Math.max(
    1,
    end - 4,
  )

  end = Math.min(
    lastPage,
    start + 4,
  )

  return Array.from(
    {
      length: end - start + 1,
    },
    (_, index) => start + index,
  )
})

function changePage(page: number): void {
  if (
    props.loading
    || page < 1
    || page > props.meta.last_page
    || page === props.meta.current_page
  ) {
    return
  }

  emit('change', page)
}
</script>

<template>
  <nav
    v-if="meta.last_page > 1"
    class="task-pagination"
    aria-label="Task list pagination"
  >
    <button
      class="task-pagination__button task-pagination__button--wide"
      type="button"
      :disabled="loading || meta.current_page <= 1"
      @click="changePage(meta.current_page - 1)"
    >
      Previous
    </button>

    <div class="task-pagination__pages">
      <button
        v-for="page in visiblePages"
        :key="page"
        class="task-pagination__button"
        :class="{
          'task-pagination__button--active':
            page === meta.current_page,
        }"
        type="button"
        :data-page="page"
        :aria-current="
          page === meta.current_page
            ? 'page'
            : undefined
        "
        :disabled="loading"
        @click="changePage(page)"
      >
        {{ page }}
      </button>
    </div>

    <button
      class="task-pagination__button task-pagination__button--wide"
      type="button"
      :disabled="
        loading
          || meta.current_page >= meta.last_page
      "
      @click="changePage(meta.current_page + 1)"
    >
      Next
    </button>
  </nav>
</template>
