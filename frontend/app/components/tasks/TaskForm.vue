<script setup lang="ts">
import type {
  Task,
  TaskFormPayload,
  TaskStatus,
} from '~/types/api'

interface TaskFormState {
  title: string
  description: string
  due_date: string
  status: TaskStatus
}

const props = withDefaults(defineProps<{
  task?: Task | null
  submitting?: boolean
  serverErrors?: Record<string, string[]>
  generalError?: string
}>(), {
  task: null,
  submitting: false,
  serverErrors: () => ({}),
  generalError: '',
})

const emit = defineEmits<{
  submit: [payload: TaskFormPayload]
  cancel: []
}>()

const statusOptions: Array<{
  value: TaskStatus
  label: string
}> = [
  {
    value: 'pending',
    label: 'Pending',
  },
  {
    value: 'in_progress',
    label: 'In progress',
  },
  {
    value: 'completed',
    label: 'Completed',
  },
]

const form = reactive<TaskFormState>({
  title: '',
  description: '',
  due_date: '',
  status: 'pending',
})

const clientErrors = ref<
  Partial<Record<keyof TaskFormState, string>>
>({})

watch(
  () => props.task,
  (task) => {
    form.title = task?.title ?? ''
    form.description = task?.description ?? ''
    form.due_date = task?.due_date ?? ''
    form.status = task?.status ?? 'pending'

    clientErrors.value = {}
  },
  {
    immediate: true,
  },
)

function fieldError(
  field: keyof TaskFormState,
): string | undefined {
  return clientErrors.value[field]
    ?? props.serverErrors[field]?.[0]
}

function validate(): boolean {
  const errors: Partial<
    Record<keyof TaskFormState, string>
  > = {}

  const title = form.title.trim()
  const description = form.description.trim()

  if (title.length < 3) {
    errors.title = 'Title must contain at least 3 characters.'
  }
  else if (title.length > 255) {
    errors.title = 'Title may not exceed 255 characters.'
  }

  if (description.length > 5000) {
    errors.description
      = 'Description may not exceed 5000 characters.'
  }

  if (
    form.due_date
    && !/^\d{4}-\d{2}-\d{2}$/.test(form.due_date)
  ) {
    errors.due_date = 'Enter a valid date.'
  }

  if (!statusOptions.some(
    option => option.value === form.status,
  )) {
    errors.status = 'Select a valid status.'
  }

  clientErrors.value = errors

  return Object.keys(errors).length === 0
}

function submit(): void {
  if (!validate()) {
    return
  }

  emit('submit', {
    title: form.title.trim(),
    description: form.description.trim() || null,
    due_date: form.due_date || null,
    status: form.status,
  })
}
</script>

<template>
  <form
    class="task-form"
    novalidate
    @submit.prevent="submit"
  >
    <p
      v-if="generalError"
      class="alert alert--error"
      role="alert"
    >
      {{ generalError }}
    </p>

    <div class="form-field">
      <label for="task-title">
        Title
      </label>

      <input
        id="task-title"
        v-model="form.title"
        name="title"
        type="text"
        maxlength="255"
        autocomplete="off"
        placeholder="What needs to be done?"
        :aria-invalid="Boolean(fieldError('title'))"
        :aria-describedby="fieldError('title')
          ? 'task-title-error'
          : undefined"
      >

      <p
        v-if="fieldError('title')"
        id="task-title-error"
        class="field-error"
      >
        {{ fieldError('title') }}
      </p>
    </div>

    <div class="form-field">
      <label for="task-description">
        Description
        <span class="label-optional">Optional</span>
      </label>

      <textarea
        id="task-description"
        v-model="form.description"
        name="description"
        maxlength="5000"
        rows="5"
        placeholder="Add useful context or notes"
        :aria-invalid="Boolean(fieldError('description'))"
        :aria-describedby="fieldError('description')
          ? 'task-description-error'
          : undefined"
      />

      <p
        v-if="fieldError('description')"
        id="task-description-error"
        class="field-error"
      >
        {{ fieldError('description') }}
      </p>
    </div>

    <div class="task-form__row">
      <div class="form-field">
        <label for="task-due-date">
          Due date
          <span class="label-optional">Optional</span>
        </label>

        <input
          id="task-due-date"
          v-model="form.due_date"
          name="due_date"
          type="date"
          :aria-invalid="Boolean(fieldError('due_date'))"
          :aria-describedby="fieldError('due_date')
            ? 'task-due-date-error'
            : undefined"
        >

        <p
          v-if="fieldError('due_date')"
          id="task-due-date-error"
          class="field-error"
        >
          {{ fieldError('due_date') }}
        </p>
      </div>

      <div class="form-field">
        <label for="task-status">
          Status
        </label>

        <select
          id="task-status"
          v-model="form.status"
          name="status"
          :aria-invalid="Boolean(fieldError('status'))"
          :aria-describedby="fieldError('status')
            ? 'task-status-error'
            : undefined"
        >
          <option
            v-for="option in statusOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>

        <p
          v-if="fieldError('status')"
          id="task-status-error"
          class="field-error"
        >
          {{ fieldError('status') }}
        </p>
      </div>
    </div>

    <div class="task-form__actions">
      <button
        class="button button--secondary"
        type="button"
        :disabled="submitting"
        @click="emit('cancel')"
      >
        Cancel
      </button>

      <button
        class="button button--primary"
        type="submit"
        :disabled="submitting"
      >
        {{
          submitting
            ? 'Saving…'
            : task
              ? 'Save changes'
              : 'Create task'
        }}
      </button>
    </div>
  </form>
</template>
