import { usePublicJobs } from './usePublicJobs'
import { useSearchStore } from '../stores/useSearchStore'
import { useRouter, useRoute } from 'vue-router'

export function useJobSearch() {
  const store = useSearchStore()
  const { jobs, paginationLinks, paginationMeta, isListLoading, loadJobs } = usePublicJobs()
  const router = useRouter()
  const route = useRoute()

  async function executeSearch(page = 1) {
    store.setPage(page)

    await loadJobs({
      q: store.q,
      category_id: store.category_id,
      location_id: store.location_id,
      work_type: store.work_type,
      experience_level: store.experience_level,
      salary_min: store.salary_min,
      salary_max: store.salary_max,
      posted_after: store.posted_after,
      page: store.page,
    })

    // Update URL query params to keep them in sync with the store
    const query: Record<string, string | number> = {}
    if (store.q) query.q = store.q
    if (store.category_id) query.category_id = store.category_id
    if (store.location_id) query.location_id = store.location_id
    if (store.work_type) query.work_type = store.work_type
    if (store.experience_level) query.experience_level = store.experience_level
    if (store.salary_min) query.salary_min = store.salary_min
    if (store.salary_max) query.salary_max = store.salary_max
    if (store.posted_after) query.posted_after = store.posted_after
    if (store.page > 1) query.page = store.page

    router.replace({ query })
  }

  function syncFromQuery() {
    store.q = (route.query.q as string) || ''
    store.category_id = route.query.category_id ? Number(route.query.category_id) : null
    store.location_id = route.query.location_id ? Number(route.query.location_id) : null
    store.work_type = (route.query.work_type as string) || null
    store.experience_level = (route.query.experience_level as string) || null
    store.salary_min = route.query.salary_min ? Number(route.query.salary_min) : null
    store.salary_max = route.query.salary_max ? Number(route.query.salary_max) : null
    store.posted_after = (route.query.posted_after as string) || null
    store.page = route.query.page ? Number(route.query.page) : 1
  }

  return {
    jobs,
    paginationLinks,
    paginationMeta,
    isListLoading,
    executeSearch,
    syncFromQuery,
    store,
  }
}
