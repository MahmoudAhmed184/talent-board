import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuth } from '../useAuth'
import { useAuthStore } from '../../stores/useAuthStore'

const { getMock, postMock } = vi.hoisted(() => ({
  getMock: vi.fn(),
  postMock: vi.fn(),
}))

vi.mock('../../../../http', () => ({
  http: {
    get: getMock,
    post: postMock,
  },
  setUnauthorizedHandler: vi.fn(),
}))

describe('useAuth', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    getMock.mockReset()
    postMock.mockReset()
  })

  it('restores authenticated session context from me endpoint', async () => {
    getMock.mockResolvedValueOnce({
      data: {
        data: {
          user: { id: 1, name: 'Candidate User', email: 'candidate@example.com' },
          role: 'candidate',
          abilities: ['jobs:view'],
          profile: null,
        },
      },
    })

    const auth = useAuth()
    const context = await auth.restoreSession()
    const store = useAuthStore()

    expect(context?.role).toBe('candidate')
    expect(store.isAuthenticated).toBe(true)
    expect(store.role).toBe('candidate')
  })

  it('logs in after preparing the spa session', async () => {
    getMock.mockResolvedValueOnce({ data: {} })
    postMock.mockResolvedValueOnce({
      data: {
        data: {
          user: { id: 2, name: 'Employer User', email: 'employer@example.com' },
          role: 'employer',
          abilities: ['employer:manage-jobs'],
          profile: { company_name: 'Acme Hiring' },
        },
      },
    })

    const auth = useAuth()
    const context = await auth.login({
      email: 'employer@example.com',
      password: 'password123',
    })
    const store = useAuthStore()

    expect(getMock).toHaveBeenCalledWith('/sanctum/csrf-cookie', {
      skipUnauthorizedHandler: true,
    })
    expect(postMock).toHaveBeenCalledWith(
      '/api/v1/auth/login',
      {
        email: 'employer@example.com',
        password: 'password123',
      },
      {
        skipUnauthorizedHandler: true,
      },
    )
    expect(context.role).toBe('employer')
    expect(store.role).toBe('employer')
    expect(store.isAuthenticated).toBe(true)
  })

  it('registers and stores the returned session context', async () => {
    getMock.mockResolvedValueOnce({ data: {} })
    postMock.mockResolvedValueOnce({
      data: {
        data: {
          user: { id: 3, name: 'New Candidate', email: 'candidate@example.com' },
          role: 'candidate',
          abilities: ['jobs:view'],
          profile: null,
        },
      },
    })

    const auth = useAuth()
    const context = await auth.register({
      role: 'candidate',
      name: 'New Candidate',
      email: 'candidate@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })
    const store = useAuthStore()

    expect(getMock).toHaveBeenCalledWith('/sanctum/csrf-cookie', {
      skipUnauthorizedHandler: true,
    })
    expect(postMock).toHaveBeenCalledWith(
      '/api/v1/auth/register',
      {
        role: 'candidate',
        name: 'New Candidate',
        email: 'candidate@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      },
      {
        skipUnauthorizedHandler: true,
      },
    )
    expect(context.role).toBe('candidate')
    expect(store.isAuthenticated).toBe(true)
  })

  it('clears local session when me endpoint fails', async () => {
    getMock.mockRejectedValueOnce(new Error('Unauthorized'))

    const auth = useAuth()
    const context = await auth.restoreSession()
    const store = useAuthStore()

    expect(context).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })
})
