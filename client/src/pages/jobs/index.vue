<script setup lang="ts">
import { onMounted } from 'vue'
import Pagination from '../../components/Pagination.vue'
import SearchFilters from '../../components/SearchFilters.vue'
import SearchLayout from '../../layouts/SearchLayout.vue'
import JobCard from '../../features/jobs/components/JobCard.vue'
import { useJobSearch } from '../../composables/useJobSearch'

const {
  jobs,
  paginationLinks,
  paginationMeta,
  isListLoading,
  executeSearch,
  syncFromQuery,
  store,
} = useJobSearch()

onMounted(async () => {
  syncFromQuery()
  await executeSearch(store.page)
})
</script>

<template>
  <section class="py-8 sm:py-10">
    <header class="mb-8 flex flex-col gap-2">
      <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
        Public jobs
      </p>
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Browse jobs</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Search approved openings, compare work mode and salary, then open the full
            listing when a role looks relevant.
          </p>
        </div>
      </div>
    </header>

    <SearchLayout>
      <template #sidebar>
        <SearchFilters @search="executeSearch(1)" />
      </template>

      <div class="grid gap-6">
        <div v-if="isListLoading" class="grid gap-3" role="status">
          <div
            v-for="index in 3"
            :key="index"
            class="h-36 animate-pulse rounded-lg border border-slate-200 bg-white"
          />
        </div>

        <div
          v-else-if="jobs.length === 0"
          class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-12 text-center"
        >
          <p class="text-base font-medium text-slate-900">No jobs found</p>
          <p class="mt-1 text-sm text-slate-600">Try adjusting your filters.</p>
        </div>

        <div v-else class="grid gap-4">
          <ul class="grid gap-4">
            <li
              v-for="job in jobs"
              :key="job.id"
              class="rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-emerald-200 hover:shadow-md"
            >
              <JobCard :job="job" />
            </li>
          </ul>

          <Pagination
            :links="paginationLinks"
            :meta="paginationMeta"
            :disabled="isListLoading"
            @page-change="executeSearch"
          />
        </div>
      </div>
    </SearchLayout>
  </section>
</template>
