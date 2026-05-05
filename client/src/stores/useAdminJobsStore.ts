import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { http } from '../http'
import { useFormErrors, type ApiErrorPayload } from '../composables/useFormErrors'
import type { JsonApiPaginatedResponse, JsonApiPaginationLinks, JsonApiPaginationMeta } from '../types/pagination'

export interface AdminJobListing {
  id: number
  title: string
  category: string | null
  location: string | null
  approval_status: 'pending' | 'approved' | 'rejected'
  published_at: string | null
  updated_at: string | null
  rejected_reason?: string | null
  employer?: {
    id: number | null
    name: string | null
    company_name: string | null
  } | null
}

export interface AdminActivitySummary {
  totals: {
    users: number
    candidates: number
    employers: number
    admins: number
    jobs: number
    jobs_pending: number
    jobs_approved: number
    jobs_rejected: number
    applications: number
  }
  recent_users: Array<{
    id: number
    name: string
    email: string
    role: string
    created_at: string
  }>
  recent_jobs: Array<{
    id: number
    title: string
    approval_status: string
    employer?: { name?: string | null } | null
    category?: { name?: string | null } | null
    location?: { name?: string | null } | null
    created_at: string
  }>
  recent_applications: Array<{
    id: number
    status: string
    submitted_at: string | null
    candidate?: { name?: string | null } | null
    employer?: { name?: string | null } | null
    job_listing?: { title?: string | null } | null
  }>
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

export const useAdminJobsStore = defineStore('admin-jobs', () => {
  const pendingJobs = ref<AdminJobListing[]>([])
  const allJobs = ref<AdminJobListing[]>([])
  const activity = ref<AdminActivitySummary | null>(null)
  const pendingPaginationLinks = ref<JsonApiPaginationLinks>(emptyLinks())
  const pendingPaginationMeta = ref<JsonApiPaginationMeta>(emptyMeta())
  const allPaginationLinks = ref<JsonApiPaginationLinks>(emptyLinks())
  const allPaginationMeta = ref<JsonApiPaginationMeta>(emptyMeta())
  const isLoading = ref(false)
  const isRefreshingActivity = ref(false)
  const isUpdating = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors<'rejected_reason'>()

  const isEmpty = computed(() => !isLoading.value && pendingJobs.value.length === 0 && allJobs.value.length === 0)

  async function loadPendingJobs(page = 1): Promise<void> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<CollectionResponse<AdminJobListing>>('/api/v1/admin/jobs/pending', {
        params: { page },
      })
      pendingJobs.value = response.data.data
      pendingPaginationLinks.value = response.data.links
      pendingPaginationMeta.value = response.data.meta
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      pendingJobs.value = []
      pendingPaginationLinks.value = emptyLinks()
      pendingPaginationMeta.value = emptyMeta()
    } finally {
      isLoading.value = false
    }
  }

  async function loadAllJobs(page = 1): Promise<void> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<CollectionResponse<AdminJobListing>>('/api/v1/admin/jobs', {
        params: { page },
      })
      allJobs.value = response.data.data
      allPaginationLinks.value = response.data.links
      allPaginationMeta.value = response.data.meta
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      allJobs.value = []
      allPaginationLinks.value = emptyLinks()
      allPaginationMeta.value = emptyMeta()
    } finally {
      isLoading.value = false
    }
  }

  async function loadActivity(): Promise<void> {
    isRefreshingActivity.value = true

    try {
      const response = await http.get<SingleResponse<AdminActivitySummary>>('/api/v1/admin/activity')
      activity.value = response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      activity.value = null
    } finally {
      isRefreshingActivity.value = false
    }
  }

  async function approveJob(jobId: number): Promise<boolean> {
    clearErrors()
    isUpdating.value = true

    try {
      const response = await http.patch<SingleResponse<AdminJobListing>>(`/api/v1/admin/jobs/${jobId}/approve`)
      applyJobUpdate(response.data.data)
      return true
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      return false
    } finally {
      isUpdating.value = false
    }
  }

  async function rejectJob(jobId: number, rejectedReason: string): Promise<boolean> {
    clearErrors()
    isUpdating.value = true

    try {
      const response = await http.patch<SingleResponse<AdminJobListing>>(`/api/v1/admin/jobs/${jobId}/reject`, {
        rejected_reason: rejectedReason.trim() || undefined,
      })
      applyJobUpdate(response.data.data)
      return true
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      return false
    } finally {
      isUpdating.value = false
    }
  }

  function applyJobUpdate(job: AdminJobListing): void {
    pendingJobs.value = pendingJobs.value.filter((item) => item.id !== job.id)
    allJobs.value = [job, ...allJobs.value.filter((item) => item.id !== job.id)]
  }

  async function refreshAll(): Promise<void> {
    await Promise.all([loadPendingJobs(), loadAllJobs(), loadActivity()])
  }

  return {
    pendingJobs,
    pendingPaginationLinks,
    pendingPaginationMeta,
    allJobs,
    allPaginationLinks,
    allPaginationMeta,
    activity,
    formError,
    isEmpty,
    isLoading,
    isRefreshingActivity,
    isUpdating,
    approveJob,
    loadActivity,
    loadAllJobs,
    loadPendingJobs,
    refreshAll,
    rejectJob,
  }
})
