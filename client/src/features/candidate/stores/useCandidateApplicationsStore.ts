import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCandidateApplications } from '../composables/useCandidateApplications'
import type { CandidateApplication } from '../types'
import { usePagination } from '../../../composables/usePagination'

export const useCandidateApplicationsStore = defineStore('candidate-applications', () => {
  const applications = ref<CandidateApplication[]>([])
  const { fetchApplications, cancelApplication: apiCancelApplication, isCancelling } = useCandidateApplications()
  
  const {
    currentPage,
    lastPage,
    perPage,
    total,
    isFirstPage,
    isLastPage,
    nextPage,
    prevPage,
    setPaginationFromMeta
  } = usePagination(loadPage)

  const isFetching = ref(false)
  const currentStatusFilter = ref<string | undefined>(undefined)

  async function loadPage(page: number) {
    isFetching.value = true
    try {
      const response = await fetchApplications(page, {
        status: currentStatusFilter.value,
      })
      applications.value = response.data
      setPaginationFromMeta(response.meta)
    } finally {
      isFetching.value = false
    }
  }

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
    isFirstPage,
    isLastPage,
    lastPage,
    loadPage,
    nextPage,
    perPage,
    prevPage,
    setFilterAndLoad,
    total,
  }
})
