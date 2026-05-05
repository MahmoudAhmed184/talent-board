<script setup lang="ts">
import { onMounted } from 'vue'
import { Briefcase } from 'lucide-vue-next'
import { useJobSearch } from '../../composables/useJobSearch'
import Pagination from '../../components/Pagination.vue'
import SearchFilters from '../../components/SearchFilters.vue'
import JobCard from '../../features/jobs/components/JobCard.vue'

const {
  jobs,
  paginationLinks,
  paginationMeta,
  isListLoading,
  executeSearch,
  syncFromQuery,
} = useJobSearch()

onMounted(async () => {
  syncFromQuery()
  await executeSearch(1)
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 md:px-8 py-8 sm:py-10 space-y-8">
    <!-- Header -->
    <header>
      <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-2">
        Public jobs
      </p>
      <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Browse jobs</h1>
      <p class="mt-2 max-w-2xl text-lg text-slate-600">
        Search approved openings, compare work mode and salary, then open the full
        listing when a role looks relevant.
      </p>
    </header>

    <!-- Layout: Sidebar Filters + Job List -->
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">
      
      <!-- Filters Sidebar -->
      <SearchFilters @search="executeSearch(1)" class="sticky top-24" />
      
      <!-- Job List Column -->
      <div class="space-y-4">
        
        <div v-if="isListLoading" class="grid gap-4">
          <div v-for="i in 3" :key="i" class="h-40 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
        </div>
        
        <div v-else-if="jobs.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <Briefcase class="w-8 h-8 text-slate-400" />
          </div>
          <h3 class="text-lg font-semibold text-slate-900">No jobs found</h3>
          <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">Try adjusting your search filters to find what you're looking for.</p>
        </div>

        <div v-else class="grid gap-4">
          <div
            v-for="job in jobs"
            :key="job.id"
            class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all"
          >
            <!-- Reuse the same JobCard component. The Apply button logic is handled internally -->
            <JobCard :job="job" />
          </div>
          
          <div v-if="paginationMeta && paginationMeta.last_page > 1" class="mt-4 flex justify-center">
            <Pagination
              :links="paginationLinks"
              :meta="paginationMeta"
              :disabled="isListLoading"
              @page-change="executeSearch"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
