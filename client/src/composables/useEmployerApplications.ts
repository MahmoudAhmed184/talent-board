import { computed, ref } from 'vue'
import { http } from '../http'
import { useFormErrors, type ApiErrorPayload } from './useFormErrors'

type ApplicationStatus = 'submitted' | 'under_review' | 'accepted' | 'rejected' | 'cancelled'
type EmployerDecision = 'accepted' | 'rejected'

export interface EmployerApplicationItem {
  id: number
  job_listing_id: number | null
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
    disk: string | null
    path: string | null
    original_name: string | null
  }
  submitted_at: string | null
  decision: {
    note: string | null
    decided_at: string | null
    decided_by_user_id: number | null
  }
}

interface CollectionResponse<TData> {
  data: TData[]
}

interface SingleResponse<TData> {
  data: TData
}

export function useEmployerApplications() {
  const list = ref<EmployerApplicationItem[]>([])
  const selected = ref<EmployerApplicationItem | null>(null)
  const isLoading = ref(false)
  const isDetailLoading = ref(false)
  const isUpdatingStatus = ref(false)
  const {
    state: { formError },
    clearErrors,
    mapApiErrors,
  } = useFormErrors<'status'>()

  const isEmpty = computed(() => !isLoading.value && list.value.length === 0)

  async function loadList(): Promise<void> {
    clearErrors()
    isLoading.value = true

    try {
      const response = await http.get<CollectionResponse<EmployerApplicationItem>>(
        '/api/v1/employer/applications',
      )
      list.value = response.data.data
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload)
      list.value = []
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

  return {
    list,
    selected,
    isLoading,
    isDetailLoading,
    isUpdatingStatus,
    isEmpty,
    formError,
    loadList,
    loadDetail,
    updateDecision,
  }
}
