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
  location_id?: number | null
  category_id?: number | null
  work_type?: string | null
  experience_level?: string | null
  salary_min?: number | null
  salary_max?: number | null
  posted_after?: string | null
  posted_before?: string | null
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
          location_id: filters.location_id || undefined,
          category_id: filters.category_id || undefined,
          work_type: filters.work_type || undefined,
          experience_level: filters.experience_level || undefined,
          salary_min: filters.salary_min || undefined,
          salary_max: filters.salary_max || undefined,
          posted_after: filters.posted_after || undefined,
          posted_before: filters.posted_before || undefined,
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
