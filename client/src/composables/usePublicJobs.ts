import { storeToRefs } from 'pinia'
import { useJobStore } from '../stores/useJobStore'
export type { PublicJobDetail, PublicJobFilters, PublicJobSummary } from '../stores/useJobStore'

export function usePublicJobs() {
  const store = useJobStore()
  const {
    formError,
    isDetailLoading,
    isListLoading,
    jobs,
    paginationLinks,
    paginationMeta,
    selectedJob,
  } = storeToRefs(store)

  return {
    jobs,
    selectedJob,
    paginationLinks,
    paginationMeta,
    isListLoading,
    isDetailLoading,
    formError,
    loadJobs: store.loadJobs,
    loadJob: store.loadJob,
  }
}
