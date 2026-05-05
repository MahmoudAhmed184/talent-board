import { ref } from 'vue'
import { http } from '../../../http'
import type { CandidateProfile, Resume, UpdateCandidateProfilePayload } from '../types'
import type { JsonApiPaginatedResponse } from '../../../types/pagination'

export function useCandidateProfile() {
  const isUpdating = ref(false)
  const isFetching = ref(false)

  async function fetchProfile(): Promise<CandidateProfile> {
    isFetching.value = true
    try {
      const response = await http.get<{ data: CandidateProfile }>('/api/v1/candidate/profile')
      return response.data.data
    } finally {
      isFetching.value = false
    }
  }

  async function updateProfile(payload: UpdateCandidateProfilePayload): Promise<CandidateProfile> {
    isUpdating.value = true
    try {
      const response = await http.patch<{ data: CandidateProfile }>('/api/v1/candidate/profile', payload)
      return response.data.data
    } finally {
      isUpdating.value = false
    }
  }

  async function fetchResumes(page = 1): Promise<JsonApiPaginatedResponse<Resume>> {
    const response = await http.get<JsonApiPaginatedResponse<Resume>>('/api/v1/candidate/resumes', {
      params: { page },
    })
    return response.data
  }

  async function deleteResume(id: number): Promise<void> {
    await http.delete(`/api/v1/candidate/resumes/${id}`)
  }

  return {
    deleteResume,
    fetchProfile,
    fetchResumes,
    isFetching,
    isUpdating,
    updateProfile,
  }
}
