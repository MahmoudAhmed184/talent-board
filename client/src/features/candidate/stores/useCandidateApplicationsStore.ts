import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCandidateApplications } from '../composables/useCandidateApplications'
import type { CandidateApplication } from '../types'
import { usePagination } from '../../../composables/usePagination'

export const useCandidateApplicationsStore = defineStore('candidate-applications', () => {
  const { fetchApplications, cancelApplication: apiCancelApplication, isCancelling } = useCandidateApplications()
  
  const currentStatusFilter = ref<string | undefined>(undefined)

  const {
    items: applications,
    links,
    meta,
    currentPage,
    lastPage,
    isLoading: isFetching,
    loadPage,
  } = usePagination<CandidateApplication>({
    fetchPage: (page) => fetchApplications(page, { status: currentStatusFilter.value })
  })

  async function setFilterAndLoad(status?: string) {
    currentStatusFilter.value = status
    await loadPage(1)
  }

  async function cancelApplication(id: number) {
    const cancelledApp = await apiCancelApplication(id)
    
    // Update local state to reflect cancellation
    const index = applications.value.findIndex(a => a.id === id)
    if (index !== -1) {
      applications.value[index] = cancelledApp
    }
  }

  return {
    applications,
    cancelApplication,
    currentPage,
    currentStatusFilter,
    isCancelling,
    isFetching,
    lastPage,
    links,
    loadPage,
    meta,
    setFilterAndLoad,
  }
})
