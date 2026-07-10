import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'
import IndexPage from '~/pages/index.vue'

describe('IndexPage', () => {
  it('renders the application name', async () => {
    const wrapper = await mountSuspended(IndexPage)

    expect(wrapper.get('h1').text()).toBe('To-Do List')
  })
})
