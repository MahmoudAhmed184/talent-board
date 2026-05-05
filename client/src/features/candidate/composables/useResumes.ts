import { ref } from 'vue'
import { http } from '../../../http'
import type { Resume } from '../types'

export function useResumes() {
  const resumes = ref<Resume[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function fetchResumes() {
    isLoading.value = true
    error.value = null
    try {
      const response = await http.get('/api/v1/candidate/resumes')
      resumes.value = response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch resumes'
    } finally {
      isLoading.value = false
    }
  }

  async function uploadResume(file: File) {
    isLoading.value = true
    error.value = null
    try {
      const formData = new FormData()
      formData.append('file', file)
      const response = await http.post('/api/v1/candidate/resumes', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      const newResume = response.data.data
      resumes.value.unshift(newResume)
      return newResume
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to upload resume'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    resumes,
    isLoading,
    error,
    fetchResumes,
    uploadResume,
  }
}
