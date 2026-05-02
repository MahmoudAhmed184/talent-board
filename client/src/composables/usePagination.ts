import { computed, shallowRef } from 'vue'
import type {
  JsonApiPaginatedResponse,
  JsonApiPaginationLinks,
  JsonApiPaginationMeta,
} from '../types/pagination'

const emptyLinks: JsonApiPaginationLinks = {
  first: null,
  last: null,
  next: null,
  prev: null,
}

const emptyMeta: JsonApiPaginationMeta = {
  current_page: 1,
  from: null,
  last_page: 1,
  per_page: 15,
  to: null,
  total: 0,
}

interface UsePaginationOptions<TItem, TParams> {
  fetchPage?: (page: number, params?: TParams) => Promise<JsonApiPaginatedResponse<TItem>>
  initialResponse?: JsonApiPaginatedResponse<TItem>
}

export function usePagination<TItem, TParams = Record<string, unknown>>(
  options: UsePaginationOptions<TItem, TParams> = {},
) {
  const items = shallowRef<TItem[]>(options.initialResponse?.data ?? [])
  const links = shallowRef<JsonApiPaginationLinks>(options.initialResponse?.links ?? emptyLinks)
  const meta = shallowRef<JsonApiPaginationMeta>(options.initialResponse?.meta ?? emptyMeta)
  const error = shallowRef<unknown | null>(null)
  const isLoading = shallowRef(false)

  const currentPage = computed(() => meta.value.current_page)
  const lastPage = computed(() => Math.max(meta.value.last_page, 1))
  const total = computed(() => meta.value.total)
  const hasNextPage = computed(() => Boolean(links.value.next))
  const hasPreviousPage = computed(() => Boolean(links.value.prev))
  const hasResults = computed(() => items.value.length > 0)

  function setResponse(response: JsonApiPaginatedResponse<TItem>) {
    items.value = response.data
    links.value = response.links
    meta.value = response.meta
    error.value = null
  }

  function reset() {
    items.value = []
    links.value = emptyLinks
    meta.value = emptyMeta
    error.value = null
    isLoading.value = false
  }

  async function loadPage(page = currentPage.value, params?: TParams) {
    if (!options.fetchPage) {
      throw new Error('usePagination requires fetchPage before loadPage can be called.')
    }

    isLoading.value = true
    error.value = null

    try {
      const response = await options.fetchPage(page, params)
      setResponse(response)

      return response
    } catch (caughtError) {
      error.value = caughtError
      throw caughtError
    } finally {
      isLoading.value = false
    }
  }

  function pageInRange(page: number) {
    return page >= 1 && page <= lastPage.value
  }

  async function handlePageChange(page: number, params?: TParams) {
    if (!pageInRange(page) || page === currentPage.value) {
      return null
    }

    return loadPage(page, params)
  }

  return {
    currentPage,
    error,
    handlePageChange,
    hasNextPage,
    hasPreviousPage,
    hasResults,
    isLoading,
    items,
    lastPage,
    links,
    loadPage,
    meta,
    pageInRange,
    reset,
    setResponse,
    total,
  }
}
