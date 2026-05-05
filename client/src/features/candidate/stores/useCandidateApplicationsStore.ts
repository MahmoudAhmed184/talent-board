import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCandidateApplications } from '../composables/useCandidateApplications'
import type { CandidateApplication } from '../types'
import { usePagination } from '../../../composables/usePagination'

export const useCandidateApplicationsStore = defineStore('candidate-applications', () => {
  const { fetchApplications, cancelApplication: apiCancelApplication, fetchAppliedJobIds: apiFetchAppliedIds, isCancelling } = useCandidateApplications()
  
  const appliedJobIds = ref<Set<number>>(new Set())
  const isFetchingIds = ref(false)
  const currentStatusFilter = ref<string | undefined>(undefined)
  const fromDate = ref<string | undefined>(undefined)
  const toDate = ref<string | undefined>(undefined)

  const {
    items: applications,
    links,
    meta,
    currentPage,
    lastPage,
    isLoading: isFetching,
    loadPage,
  } = usePagination<CandidateApplication>({
    fetchPage: (page) => fetchApplications(page, { 
      status: currentStatusFilter.value,
      from_date: fromDate.value,
      to_date: toDate.value,
    })
  })

  async function setFilterAndLoad(status?: string, from?: string, to?: string) {
    if (status !== undefined) currentStatusFilter.value = status
    if (from !== undefined) fromDate.value = from
    if (to !== undefined) toDate.value = to
    await loadPage(1)
  }

  function resetFilters() {
    currentStatusFilter.value = undefined
    fromDate.value = undefined
    toDate.value = undefined
    loadPage(1)
  }

  async function cancelApplication(id: number) {
    const cancelledApp = await apiCancelApplication(id)
    
    // Update local state to reflect cancellation
    const index = applications.value.findIndex(a => a.id === id)
    if (index !== -1) {
      applications.value[index] = cancelledApp
    }
  }

  async function fetchAppliedJobIds() {
    isFetchingIds.value = true
    try {
      const ids = await apiFetchAppliedIds()
      appliedJobIds.value = new Set(ids)
    } finally {
      isFetchingIds.value = false
    }
  }

  function isJobApplied(jobId: number | string | undefined) {
    if (jobId === undefined) return false
    return appliedJobIds.value.has(Number(jobId))
  }

  function markJobAsApplied(jobId: number | string) {
    appliedJobIds.value.add(Number(jobId))
  }

  return {
    applications,
    cancelApplication,
    currentPage,
    currentStatusFilter,
    fromDate,
    toDate,
    isCancelling,
    isFetching,
    lastPage,
    links,
    loadPage,
    meta,
    setFilterAndLoad,
    resetFilters,
    appliedJobIds,
    isFetchingIds,
    fetchAppliedJobIds,
    isJobApplied,
    markJobAsApplied,
  }
})
