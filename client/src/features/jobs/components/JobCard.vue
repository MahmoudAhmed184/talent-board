<script setup lang="ts">
import { Briefcase, MapPin, DollarSign, Send, ArrowRight } from 'lucide-vue-next'
import { type PublicJobSummary } from '../../../composables/usePublicJobs'
import {
  sentenceCase,
  formatSalaryRange,
  descriptionSnippet,
  formatPostedDate,
} from '../utils/formatters'
import { useAuthStore } from '../../auth/stores/useAuthStore'

const authStore = useAuthStore()

const { job, hideApply = false } = defineProps<{
  job: PublicJobSummary
  hideApply?: boolean
}>()

const emit = defineEmits<{
  (e: 'apply', job: PublicJobSummary): void
}>()
</script>

<template>
  <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6 group">
    <div class="flex-1 min-w-0">
      <div class="flex items-center gap-2 mb-2 text-xs font-semibold uppercase tracking-wider text-emerald-600">
        <span>{{ job.employer?.company_name || 'Company' }}</span>
        <span class="text-slate-300">&bull;</span>
        <span>{{ job.category || 'General' }}</span>
      </div>
      
      <h2 class="text-xl font-bold text-slate-900 truncate mb-2 group-hover:text-emerald-700 transition-colors">
        <RouterLink :to="`/jobs/${job.id}`">
          {{ job.title }}
        </RouterLink>
      </h2>
      
      <p class="text-sm text-slate-600 line-clamp-2 mb-4">
        {{ descriptionSnippet(job.description) }}
      </p>
      
      <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-600">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100">
          <MapPin class="w-3.5 h-3.5 text-slate-400" />
          {{ job.location || 'Remote' }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100">
          <Briefcase class="w-3.5 h-3.5 text-slate-400" />
          {{ sentenceCase(job.work_type) }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100">
          <DollarSign class="w-3.5 h-3.5 text-slate-400" />
          {{ formatSalaryRange(job.salary_min, job.salary_max) }}
        </span>
      </div>
    </div>
    
    <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between shrink-0 gap-4">
      <p class="text-xs text-slate-500 font-medium">{{ formatPostedDate(job.published_at) }}</p>
      <div class="flex gap-2">
        <RouterLink
          v-if="!authStore.isAuthenticated || authStore.role !== 'candidate'"
          :to="`/jobs/${job.id}`"
          class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm gap-2"
        >
          View Details
          <ArrowRight class="w-4 h-4" />
        </RouterLink>
        <button
          v-else-if="!hideApply"
          @click="emit('apply', job)"
          class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm gap-2"
        >
          Apply Now
          <Send class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
