import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { http } from '../http'
import { useFormErrors, type ApiErrorPayload } from '../composables/useFormErrors'
import type { EmployerJobFormData, EmployerJobPayload } from '../composables/useEmployerJobForm'
import type { JsonApiPaginatedResponse, JsonApiPaginationLinks, JsonApiPaginationMeta } from '../types/pagination'

export type ApprovalStatus = 'pending' | 'approved' | 'rejected'

export interface EmployerJob {
  id: number
  title: string
  description: string
  responsibilities: string | null
  qualifications: string | null
  location_id: number | null
  location: string
  category_id: number | null
  category: string
  work_type: string
  experience_level: string
  salary_min: number | null
  salary_max: number | null
  approval_status: ApprovalStatus
  published_at: string | null
  expires_at: string | null
  created_at: string | null
  updated_at: string | null
}

type CollectionResponse<TData> = JsonApiPaginatedResponse<TData>

interface SingleResponse<TData> {
  data: TData
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

function formatDateForInput(value: string | null): string {
  return value ? value.slice(0, 10) : ''
}

export function toEmployerJobFormData(job: EmployerJob): EmployerJobFormData {
  return {
    title: job.title,
    description: job.description,
    responsibilities: job.responsibilities ?? '',
    qualifications: job.qualifications ?? '',
    locationId: job.location_id === null ? '' : String(job.location_id),
    categoryId: job.category_id === null ? '' : String(job.category_id),
    workType: job.work_type,
    experienceLevel: job.experience_level,
    salaryMin: job.salary_min === null ? '' : String(job.salary_min),
    salaryMax: job.salary_max === null ? '' : String(job.salary_max),
    expiresAt: formatDateForInput(job.expires_at),
  }
}

export const useEmployerJobsStore = defineStore('employer-jobs', () => {
  const jobs = ref<EmployerJob[]>([])
  const currentJob = ref<EmployerJob | null>(null)
  const paginationLinks = ref<JsonApiPaginationLinks>(emptyLinks())
  const paginationMeta = ref<JsonApiPaginationMeta>(emptyMeta())
  const isLoading = ref(false)
  const isSaving = ref(false)
  const isDeleting = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors()

  const isEmpty = computed(() => !isLoading.value && jobs.value.length === 0)

  async function fetchJobs(page = 1): Promise<void> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<CollectionResponse<EmployerJob>>('/api/v1/employer/jobs', {
        params: { page },
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
      isLoading.value = false
    }
  }

  async function fetchJob(jobId: string | number): Promise<EmployerJob | null> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<SingleResponse<EmployerJob>>(`/api/v1/employer/jobs/${jobId}`)
      currentJob.value = response.data.data
      return currentJob.value
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      currentJob.value = null
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function createJob(payload: EmployerJobPayload): Promise<EmployerJob> {
    clearErrors()
    isSaving.value = true

    try {
      const response = await http.post<SingleResponse<EmployerJob>>('/api/v1/employer/jobs', payload)
      currentJob.value = response.data.data
      jobs.value = [response.data.data, ...jobs.value]
      return response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function updateJob(jobId: string | number, payload: EmployerJobPayload): Promise<EmployerJob> {
    clearErrors()
    isSaving.value = true

    try {
      const response = await http.patch<SingleResponse<EmployerJob>>(
        `/api/v1/employer/jobs/${jobId}`,
        payload,
      )
      currentJob.value = response.data.data
      jobs.value = jobs.value.map((job) => (job.id === response.data.data.id ? response.data.data : job))
      return response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      throw error
    } finally {
      isSaving.value = false
    }
  }

  async function deleteJob(jobId: number): Promise<void> {
    clearErrors()
    isDeleting.value = true

    try {
      await http.delete(`/api/v1/employer/jobs/${jobId}`)
      jobs.value = jobs.value.filter((job) => job.id !== jobId)
      if (currentJob.value?.id === jobId) {
        currentJob.value = null
      }
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      throw error
    } finally {
      isDeleting.value = false
    }
  }

  return {
    jobs,
    currentJob,
    paginationLinks,
    paginationMeta,
    formError,
    isDeleting,
    isEmpty,
    isLoading,
    isSaving,
    createJob,
    deleteJob,
    fetchJob,
    fetchJobs,
    updateJob,
  }
})
