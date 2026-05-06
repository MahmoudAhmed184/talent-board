import { ref } from 'vue'
import { http } from '../../../http'
import type { ApplicationSubmissionResponse } from '../types'

export interface SubmitApplicationPayload {
  submission_mode: 'resume' | 'contact'
  resume_id?: number | null
  use_profile_contact: boolean
  cover_letter?: string | null
}

export function useApplicationSubmit() {
  const isSubmitting = ref(false)

  async function submitApplication(jobId: string | number, payload: SubmitApplicationPayload): Promise<ApplicationSubmissionResponse> {
    isSubmitting.value = true
    try {
      const response = await http.post<{ data: ApplicationSubmissionResponse }>(`/api/v1/jobs/${jobId}/applications`, payload)
      return response.data.data
    } finally {
      isSubmitting.value = false
    }
  }

  return {
    isSubmitting,
    submitApplication,
  }
}
