import { ref } from 'vue'
import { http } from '../../../http'
import type { CandidateApplication } from '../types'
import type { JsonApiPaginatedResponse } from '../../../types/pagination'

interface FetchApplicationsParams {
  page?: number
  status?: string
}

export function useCandidateApplications() {
  const isCancelling = ref(false)

  async function fetchApplications(page = 1, params?: FetchApplicationsParams): Promise<JsonApiPaginatedResponse<CandidateApplication>> {
    const query = { page, ...params }
    const response = await http.get<JsonApiPaginatedResponse<CandidateApplication>>('/api/v1/candidate/applications', {
      params: query,
    })
    return response.data
  }

  async function cancelApplication(id: number): Promise<CandidateApplication> {
    isCancelling.value = true
    try {
      const response = await http.delete<{ data: CandidateApplication }>(`/api/v1/candidate/applications/${id}`)
      return response.data.data
    } finally {
      isCancelling.value = false
    }
  }

  return {
    cancelApplication,
    fetchApplications,
    isCancelling,
  }
}
