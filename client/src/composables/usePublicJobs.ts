import { ref } from 'vue'
import { http } from '../http'
import { useFormErrors, type ApiErrorPayload } from './useFormErrors'

export interface PublicJobSummary {
  id: number
  title: string
  location?: string | null
  work_type?: string | null
  experience_level?: string | null
  published_at?: string | null
  salary_min?: number | null
  salary_max?: number | null
}

export interface PublicJobDetail extends PublicJobSummary {
  description?: string | null
  responsibilities?: string | null
  qualifications?: string | null
  employer?: {
    company_name?: string | null
    logo?: {
      path?: string | null
    }
  } | null
}

interface PublicCollectionResponse {
  data: PublicJobSummary[]
}

interface PublicDetailResponse {
  data: PublicJobDetail
}

export function usePublicJobs() {
  const jobs = ref<PublicJobSummary[]>([])
  const selectedJob = ref<PublicJobDetail | null>(null)
  const isListLoading = ref(false)
  const isDetailLoading = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors<'query'>()

  async function loadJobs(query = ''): Promise<void> {
    clearErrors()
    isListLoading.value = true

    try {
      const response = await http.get<PublicCollectionResponse>('/api/v1/jobs', {
        params: {
          q: query.trim() || undefined,
        },
      })
      jobs.value = response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      jobs.value = []
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
    isListLoading,
    isDetailLoading,
    formError,
    loadJobs,
    loadJob,
  }
}
