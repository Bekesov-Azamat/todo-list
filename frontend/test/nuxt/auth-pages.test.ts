import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'
import LoginPage from '~/pages/login.vue'
import RegisterPage from '~/pages/register.vue'

describe('Authentication pages', () => {
  it('renders the login form with correct autocomplete attributes', async () => {
    const wrapper = await mountSuspended(LoginPage)

    expect(wrapper.get('h1').text()).toBe('Sign in to your account')
    expect(wrapper.get('input[name="email"]').attributes('autocomplete'))
      .toBe('email')
    expect(wrapper.get('input[name="password"]').attributes('autocomplete'))
      .toBe('current-password')
  })

  it('renders the registration form with password confirmation', async () => {
    const wrapper = await mountSuspended(RegisterPage)

    expect(wrapper.get('h1').text()).toBe('Create your account')
    expect(wrapper.get('input[name="name"]').attributes('autocomplete'))
      .toBe('name')
    expect(
      wrapper
        .get('input[name="password_confirmation"]')
        .attributes('autocomplete'),
    ).toBe('new-password')
  })
})
