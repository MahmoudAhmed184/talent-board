import { computed, ref } from 'vue'
import { http } from '../http'
import { useFormErrors, type ApiErrorPayload } from './useFormErrors'
import type { JsonApiPaginatedResponse, JsonApiPaginationLinks, JsonApiPaginationMeta } from '../types/pagination'

type ApplicationStatus = 'submitted' | 'under_review' | 'accepted' | 'rejected' | 'cancelled'
type EmployerDecision = 'accepted' | 'rejected'

export interface EmployerApplicationItem {
  id: number
  job_listing_id: number | null
  job_listing?: {
    id: number | null
    title: string | null
  }
  status: ApplicationStatus
  cover_letter: string | null
  contact_email: string | null
  contact_phone: string | null
  candidate: {
    id: number
    name: string | null
    email: string | null
  }
  resume: {
    original_name: string | null
  }
  submitted_at: string | null
  decision: {
    note: string | null
    decided_at: string | null
    decided_by_user_id: number | null
  }
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

export function useEmployerApplications() {
  const list = ref<EmployerApplicationItem[]>([])
  const selected = ref<EmployerApplicationItem | null>(null)
  const paginationLinks = ref<JsonApiPaginationLinks>(emptyLinks())
  const paginationMeta = ref<JsonApiPaginationMeta>(emptyMeta())
  const isLoading = ref(false)
  const isDetailLoading = ref(false)
  const isUpdatingStatus = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors<'status'>()

  const isEmpty = computed(() => !isLoading.value && list.value.length === 0)

  async function loadList(page = 1): Promise<void> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<CollectionResponse<EmployerApplicationItem>>(
        '/api/v1/employer/applications',
        { params: { page } },
      )
      list.value = response.data.data
      paginationLinks.value = response.data.links
      paginationMeta.value = response.data.meta
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      list.value = []
      paginationLinks.value = emptyLinks()
      paginationMeta.value = emptyMeta()
    } finally {
      isLoading.value = false
    }
  }

  async function loadDetail(applicationId: number): Promise<void> {
    clearErrors()
    isDetailLoading.value = true

    try {
      const response = await http.get<SingleResponse<EmployerApplicationItem>>(
        `/api/v1/employer/applications/${applicationId}`,
      )
      selected.value = response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      selected.value = null
    } finally {
      isDetailLoading.value = false
    }
  }

  async function updateDecision(
    applicationId: number,
    decision: EmployerDecision,
    decisionNote: string,
  ): Promise<boolean> {
    clearErrors()
    isUpdatingStatus.value = true

    try {
      const response = await http.patch<SingleResponse<EmployerApplicationItem>>(
        `/api/v1/employer/applications/${applicationId}/status`,
        {
          status: decision,
          decision_note: decisionNote.trim() || undefined,
        },
      )

      const updated = response.data.data
      selected.value = updated
      list.value = list.value.map((item) => (item.id === updated.id ? updated : item))
      return true
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      return false
    } finally {
      isUpdatingStatus.value = false
    }
  }

  function applyStatusUpdate(payload: {
    application_id: number
    status: ApplicationStatus
    changed_at: string
  }): void {
    const updateItem = (item: EmployerApplicationItem): EmployerApplicationItem =>
      item.id === payload.application_id
        ? {
            ...item,
            status: payload.status,
            decision: {
              ...item.decision,
              decided_at: payload.changed_at,
            },
          }
        : item

    list.value = list.value.map(updateItem)
    if (selected.value?.id === payload.application_id) {
      selected.value = updateItem(selected.value)
    }
  }

  return {
    list,
    selected,
    paginationLinks,
    paginationMeta,
    isLoading,
    isDetailLoading,
    isUpdatingStatus,
    isEmpty,
    formError,
    loadList,
    loadDetail,
    updateDecision,
    applyStatusUpdate,
  }
}
