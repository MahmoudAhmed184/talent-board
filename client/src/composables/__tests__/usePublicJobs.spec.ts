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
        links: {
          first: '/api/v1/jobs?page=1',
          last: '/api/v1/jobs?page=3',
          next: '/api/v1/jobs?page=2',
          prev: null,
        },
        meta: {
          current_page: 1,
          from: 1,
          last_page: 3,
          per_page: 15,
          to: 1,
          total: 31,
        },
      },
    })

    const jobs = usePublicJobs()
    await jobs.loadJobs({ q: 'frontend', page: 1, per_page: 15 })

    expect(jobs.jobs.value).toHaveLength(1)
    expect(jobs.jobs.value[0]?.title).toBe('Frontend Engineer')
    expect(jobs.paginationLinks.value.next).toBe('/api/v1/jobs?page=2')
    expect(jobs.paginationMeta.value.total).toBe(31)
    expect(jobs.formError.value).toBe(null)
    expect(getMock).toHaveBeenCalledWith('/api/v1/jobs', {
      params: {
        q: 'frontend',
        location: undefined,
        category: undefined,
        work_type: undefined,
        experience_level: undefined,
        date_posted: undefined,
        page: 1,
        per_page: 15,
      },
    })
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
    expect(jobs.paginationMeta.value.total).toBe(0)
    expect(jobs.formError.value).toBe('Unexpected upstream failure.')
  })
})
