import { ref } from 'vue'
import { http } from '../http'
import type {
  JsonApiPaginatedResponse,
  JsonApiPaginationLinks,
  JsonApiPaginationMeta,
} from '../types/pagination'
import { useFormErrors, type ApiErrorPayload } from './useFormErrors'

export interface PublicJobSummary {
  id: number
  title: string
  description?: string | null
  category?: string | null
  location?: string | null
  work_type?: string | null
  experience_level?: string | null
  published_at?: string | null
  salary_min?: number | null
  salary_max?: number | null
  employer?: {
    company_name?: string | null
    logo?: {
      path?: string | null
    }
  } | null
}

export interface PublicJobFilters {
  q?: string
  location?: string
  category?: string
  work_type?: string
  experience_level?: string
  date_posted?: string
  page?: number
  per_page?: number
}

export interface PublicJobDetail extends PublicJobSummary {
  responsibilities?: string | null
  qualifications?: string | null
}

type PublicCollectionResponse = JsonApiPaginatedResponse<PublicJobSummary>

interface PublicDetailResponse {
  data: PublicJobDetail
}

function emptyLinks(): JsonApiPaginationLinks {
  return {
    first: null,
    last: null,
    next: null,
    prev: null,
  }
}

function emptyMeta(): JsonApiPaginationMeta {
  return {
    current_page: 1,
    from: null,
    last_page: 1,
    per_page: 15,
    to: null,
    total: 0,
  }
}

export function usePublicJobs() {
  const jobs = ref<PublicJobSummary[]>([])
  const selectedJob = ref<PublicJobDetail | null>(null)
  const paginationLinks = ref<JsonApiPaginationLinks>(emptyLinks())
  const paginationMeta = ref<JsonApiPaginationMeta>(emptyMeta())
  const isListLoading = ref(false)
  const isDetailLoading = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors<'query'>()

  async function loadJobs(filters: PublicJobFilters = {}): Promise<void> {
    clearErrors()
    isListLoading.value = true

    try {
      const response = await http.get<PublicCollectionResponse>('/api/v1/jobs', {
        params: {
          q: filters.q?.trim() || undefined,
          location: filters.location?.trim() || undefined,
          category: filters.category?.trim() || undefined,
          work_type: filters.work_type || undefined,
          experience_level: filters.experience_level || undefined,
          date_posted: filters.date_posted || undefined,
          page: filters.page || undefined,
          per_page: filters.per_page || undefined,
        },
      })
      jobs.value = response.data.data
      paginationLinks.value = response.data.links
      paginationMeta.value = response.data.meta
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      jobs.value = []
      paginationLinks.value = emptyLinks()
      paginationMeta.value = emptyMeta()
    } finally {
      isListLoading.value = false
    }
  }

  async function loadJob(jobId: string | number): Promise<void> {
    clearErrors()
    isDetailLoading.value = true

    try {
      const response = await http.get<PublicDetailResponse>(`/api/v1/jobs/${jobId}`)
      selectedJob.value = response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      selectedJob.value = null
    } finally {
      isDetailLoading.value = false
    }
  }

  return {
    jobs,
    selectedJob,
    paginationLinks,
    paginationMeta,
    isListLoading,
    isDetailLoading,
    formError,
    loadJobs,
    loadJob,
  }
}
