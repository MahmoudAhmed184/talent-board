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

  it('clears local session when me endpoint fails', async () => {
    getMock.mockRejectedValueOnce(new Error('Unauthorized'))

    const auth = useAuth()
    const context = await auth.restoreSession()
    const store = useAuthStore()

    expect(context).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })
})
