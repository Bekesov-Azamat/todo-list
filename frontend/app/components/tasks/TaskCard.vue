<script setup lang="ts">
import type {
  Task,
  TaskStatus,
} from '~/types/api'

const props = withDefaults(defineProps<{
  task: Task
  deleting?: boolean
}>(), {
  deleting: false,
})

const emit = defineEmits<{
  edit: [task: Task]
  remove: [task: Task]
}>()

const statusLabels: Record<TaskStatus, string> = {
  pending: 'Pending',
  in_progress: 'In progress',
  completed: 'Completed',
}

const statusLabel = computed(
  () => statusLabels[props.task.status],
)

const formattedDueDate = computed(() => {
  if (!props.task.due_date) {
    return null
  }

  return new Intl.DateTimeFormat('en', {
    dateStyle: 'medium',
  }).format(
    new Date(`${props.task.due_date}T00:00:00`),
  )
})
</script>

<template>
  <article
    :class="[
      'task-card',
      `task-card--${task.status}`,
    ]"
  >
    <div class="task-card__header">
      <span
        :class="[
          'task-status',
          `task-status--${task.status}`,
        ]"
      >
        {{ statusLabel }}
      </span>

      <time
        v-if="task.due_date && formattedDueDate"
        class="task-due-date"
        :datetime="task.due_date"
      >
        Due {{ formattedDueDate }}
      </time>

      <span
        v-else
        class="task-due-date task-due-date--empty"
      >
        No deadline
      </span>
    </div>

    <div class="task-card__body">
      <h2>{{ task.title }}</h2>

      <p
        v-if="task.description"
        class="task-card__description"
      >
        {{ task.description }}
      </p>

      <p
        v-else
        class="task-card__description task-card__description--empty"
      >
        No description
      </p>
    </div>

    <div class="task-card__actions">
      <button
        class="button button--secondary button--small"
        data-testid="edit-task"
        type="button"
        :disabled="deleting"
        @click="emit('edit', task)"
      >
        Edit
      </button>

      <button
        class="button button--danger button--small"
        data-testid="delete-task"
        type="button"
        :disabled="deleting"
        @click="emit('remove', task)"
      >
        {{ deleting ? 'Deleting…' : 'Delete' }}
      </button>
    </div>
  </article>
</template>
