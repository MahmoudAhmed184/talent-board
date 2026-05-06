import { storeToRefs } from 'pinia'
import { useAdminJobsStore } from '../stores/useAdminJobsStore'

export function useAdminJobModeration() {
  const store = useAdminJobsStore()
  const {
    activity,
    allJobs,
    allPaginationLinks,
    allPaginationMeta,
    formError,
    isEmpty,
    isLoading,
    isRefreshingActivity,
    isUpdating,
    pendingJobs,
    pendingPaginationLinks,
    pendingPaginationMeta,
  } = storeToRefs(store)

  return {
    activity,
    allJobs,
    allPaginationLinks,
    allPaginationMeta,
    formError,
    isEmpty,
    isLoading,
    isRefreshingActivity,
    isUpdating,
    pendingJobs,
    pendingPaginationLinks,
    pendingPaginationMeta,
    approveJob: store.approveJob,
    loadActivity: store.loadActivity,
    loadAllJobs: store.loadAllJobs,
    loadPendingJobs: store.loadPendingJobs,
    refreshAll: store.refreshAll,
    rejectJob: store.rejectJob,
  }
}
