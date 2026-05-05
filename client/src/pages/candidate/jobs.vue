<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Briefcase, FileText, CheckCircle } from 'lucide-vue-next'
import { useJobSearch } from '../../composables/useJobSearch'
import Pagination from '../../components/Pagination.vue'
import AppModal from '../../components/AppModal.vue'
import SearchFilters from '../../components/SearchFilters.vue'
import JobCard from '../../features/jobs/components/JobCard.vue'
import JobApplicationModal from '../../features/candidate/components/JobApplicationModal.vue'
import { useToast } from '../../composables/useToast'
import { http } from '../../http'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'

const {
  jobs,
  paginationLinks,
  paginationMeta,
  isListLoading,
  executeSearch,
  store,
} = useJobSearch()

const profileStore = useCandidateProfileStore()
const { success: showSuccess, error: showError } = useToast()

const selectedJob = ref<any>(null)
const isApplyModalOpen = ref(false)

onMounted(async () => {
  await executeSearch(1)
})

function openApplyModal(job: any) {
  selectedJob.value = job
  isApplyModalOpen.value = true
}

function closeApplyModal() {
  isApplyModalOpen.value = false
}

function handleApplicationSuccess() {
  // Refresh jobs to update "Applied" status if needed
  executeSearch(paginationMeta.value?.current_page || 1)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Browse Jobs</h1>
      <p class="mt-1 text-sm text-slate-500">Discover and apply to your next great opportunity.</p>
    </div>

    <!-- Filters Topbar -->
    <SearchFilters layout="topbar" @search="executeSearch(1)" />
    
    <!-- Job List -->
    <div class="flex-1 min-w-0 space-y-4 w-full">
      
      <div v-if="isListLoading" class="grid gap-4">
        <div v-for="i in 3" :key="i" class="h-40 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
      </div>
      
      <div v-else-if="jobs.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <Briefcase class="w-8 h-8 text-slate-400" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900">No jobs found</h3>
        <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">Try adjusting your search filters to find what you're looking for.</p>
        <button 
          v-if="store.activeFiltersCount > 0"
          @click="store.resetFilters(); executeSearch(1);"
          class="mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-700"
        >
          Clear all filters
        </button>
      </div>

      <div v-else class="grid gap-4">
        <div
          v-for="job in jobs"
          :key="job.id"
          class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all"
        >
          <JobCard :job="job" @apply="openApplyModal" />
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

    <!-- Apply Modal -->
    <JobApplicationModal
      v-if="selectedJob"
      :is-open="isApplyModalOpen"
      :job="selectedJob"
      @close="closeApplyModal"
      @success="handleApplicationSuccess"
    />
  </div>
</template>
