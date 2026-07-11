import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'
import TaskCard from '~/components/tasks/TaskCard.vue'
import TaskFilters from '~/components/tasks/TaskFilters.vue'
import TaskForm from '~/components/tasks/TaskForm.vue'
import TaskPagination from '~/components/tasks/TaskPagination.vue'
import type {
  PaginationMeta,
  Task,
} from '~/types/api'

describe('Task components', () => {
  it('emits a normalized task payload from the form', async () => {
    const wrapper = await mountSuspended(TaskForm)

    await wrapper
      .get('input[name="title"]')
      .setValue('  Write project documentation  ')

    await wrapper
      .get('textarea[name="description"]')
      .setValue('  Explain installation steps.  ')

    await wrapper
      .get('input[name="due_date"]')
      .setValue('2026-08-20')

    await wrapper
      .get('select[name="status"]')
      .setValue('in_progress')

    await wrapper.get('form').trigger('submit')

    expect(wrapper.emitted('submit')?.[0]?.[0])
      .toEqual({
        title: 'Write project documentation',
        description: 'Explain installation steps.',
        due_date: '2026-08-20',
        status: 'in_progress',
      })
  })

  it('does not submit a title shorter than three characters', async () => {
    const wrapper = await mountSuspended(TaskForm)

    await wrapper
      .get('input[name="title"]')
      .setValue('ab')

    await wrapper.get('form').trigger('submit')

    expect(wrapper.emitted('submit')).toBeUndefined()

    expect(wrapper.text()).toContain(
      'Title must contain at least 3 characters.',
    )
  })

  it('renders task data and emits card actions', async () => {
    const task: Task = {
      id: 15,
      title: 'Review Sprint 7',
      description: 'Check the dashboard manually.',
      due_date: '2026-08-25',
      status: 'pending',
      created_at: '2026-07-11T00:00:00.000000Z',
      updated_at: '2026-07-11T00:00:00.000000Z',
    }

    const wrapper = await mountSuspended(TaskCard, {
      props: {
        task,
      },
    })

    expect(wrapper.get('h2').text())
      .toBe('Review Sprint 7')

    expect(wrapper.get('time').attributes('datetime'))
      .toBe('2026-08-25')

    await wrapper
      .get('[data-testid="edit-task"]')
      .trigger('click')

    await wrapper
      .get('[data-testid="delete-task"]')
      .trigger('click')

    expect(wrapper.emitted('edit')?.[0]?.[0])
      .toEqual(task)

    expect(wrapper.emitted('remove')?.[0]?.[0])
      .toEqual(task)
  })

  it('emits search, status, sort and reset changes', async () => {
    const wrapper = await mountSuspended(TaskFilters, {
      props: {
        search: 'sprint',
        status: 'pending',
        sort: 'due_date_asc',
        loading: false,
      },
    })

    await wrapper
      .get('input[name="search"]')
      .setValue('documentation')

    await wrapper
      .get('select[name="filter_status"]')
      .setValue('completed')

    await wrapper
      .get('select[name="sort"]')
      .setValue('status_desc')

    await wrapper
      .get('.task-filters__reset')
      .trigger('click')

    expect(wrapper.emitted('update:search')?.[0])
      .toEqual(['documentation'])

    expect(wrapper.emitted('update:status')?.[0])
      .toEqual(['completed'])

    expect(wrapper.emitted('update:sort')?.[0])
      .toEqual(['status_desc'])

    expect(wrapper.emitted('reset')).toHaveLength(1)
  })

  it('emits the selected pagination page', async () => {
    const meta: PaginationMeta = {
      current_page: 1,
      from: 1,
      last_page: 4,
      path: '/api/tasks',
      per_page: 6,
      to: 6,
      total: 21,
    }

    const wrapper = await mountSuspended(TaskPagination, {
      props: {
        meta,
        loading: false,
      },
    })

    await wrapper
      .get('[data-page="2"]')
      .trigger('click')

    expect(wrapper.emitted('change')?.[0])
      .toEqual([2])

    expect(
      wrapper
        .get('[data-page="1"]')
        .attributes('aria-current'),
    ).toBe('page')
  })
})
