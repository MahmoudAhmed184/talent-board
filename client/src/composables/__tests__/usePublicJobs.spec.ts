import { beforeEach, describe, expect, it, vi } from 'vitest'
import { usePublicJobs } from '../usePublicJobs'

const { getMock } = vi.hoisted(() => ({
  getMock: vi.fn(),
}))

vi.mock('../../http', () => ({
  http: {
    get: getMock,
  },
}))

describe('usePublicJobs', () => {
  beforeEach(() => {
    getMock.mockReset()
  })

  it('loads public jobs into list state', async () => {
    getMock.mockResolvedValueOnce({
      data: {
        data: [{ id: 10, title: 'Frontend Engineer' }],
      },
    })

    const jobs = usePublicJobs()
    await jobs.loadJobs('frontend')

    expect(jobs.jobs.value).toHaveLength(1)
    expect(jobs.jobs.value[0]?.title).toBe('Frontend Engineer')
    expect(jobs.formError.value).toBe(null)
  })

  it('returns empty list and error message when request fails', async () => {
    getMock.mockRejectedValueOnce({
      response: {
        status: 500,
        data: {
          message: 'Unexpected upstream failure.',
        },
      },
    })

    const jobs = usePublicJobs()
    await jobs.loadJobs()

    expect(jobs.jobs.value).toEqual([])
    expect(jobs.formError.value).toBe('Unexpected upstream failure.')
  })
})
