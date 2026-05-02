<script setup lang="ts">
import { computed } from 'vue'
import type { JsonApiPaginationLinks, JsonApiPaginationMeta } from '../types/pagination'

const {
  disabled = false,
  links,
  meta,
} = defineProps<{
  disabled?: boolean
  links: JsonApiPaginationLinks
  meta: JsonApiPaginationMeta
}>()

const emit = defineEmits<{
  'page-change': [page: number]
}>()

const currentPage = computed(() => meta.current_page)
const lastPage = computed(() => Math.max(meta.last_page, 1))
const hasMultiplePages = computed(() => lastPage.value > 1)
const visiblePages = computed(() => {
  const visibleCount = Math.min(5, lastPage.value)
  const maxStart = Math.max(lastPage.value - visibleCount + 1, 1)
  const start = Math.min(Math.max(currentPage.value - 2, 1), maxStart)

  return Array.from({ length: visibleCount }, (_, index) => start + index)
})
const summary = computed(() => {
  if (meta.total === 0) {
    return 'No results'
  }

  return `Showing ${meta.from ?? 0}-${meta.to ?? 0} of ${meta.total}`
})

function pageFromUrl(url: string | null | undefined, fallback: number) {
  if (!url) {
    return fallback
  }

  try {
    const parsedUrl = new URL(url, window.location.origin)
    const page = Number(parsedUrl.searchParams.get('page'))

    return Number.isFinite(page) && page > 0 ? page : fallback
  } catch {
    return fallback
  }
}

function goToPage(page: number) {
  if (disabled || page < 1 || page > lastPage.value || page === currentPage.value) {
    return
  }

  emit('page-change', page)
}
</script>

<template>
  <nav
    v-if="hasMultiplePages"
    class="flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
    aria-label="Pagination"
  >
    <p class="min-h-6 text-sm text-slate-600">
      {{ summary }}
    </p>

    <div class="flex flex-wrap items-center gap-2">
      <button
        type="button"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
        :disabled="disabled || !links.prev"
        aria-label="First page"
        @click="goToPage(pageFromUrl(links.first, 1))"
      >
        First
      </button>
      <button
        type="button"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
        :disabled="disabled || !links.prev"
        aria-label="Previous page"
        @click="goToPage(pageFromUrl(links.prev, currentPage - 1))"
      >
        Prev
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        type="button"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border px-3 text-sm font-semibold"
        :class="page === currentPage ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'"
        :aria-current="page === currentPage ? 'page' : undefined"
        :disabled="disabled || page === currentPage"
        @click="goToPage(page)"
      >
        {{ page }}
      </button>

      <button
        type="button"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
        :disabled="disabled || !links.next"
        aria-label="Next page"
        @click="goToPage(pageFromUrl(links.next, currentPage + 1))"
      >
        Next
      </button>
      <button
        type="button"
        class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
        :disabled="disabled || !links.next"
        aria-label="Last page"
        @click="goToPage(pageFromUrl(links.last, lastPage))"
      >
        Last
      </button>
    </div>
  </nav>
</template>
