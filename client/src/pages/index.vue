<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { usePublicJobs } from '../composables/usePublicJobs'
import { useAuthStore } from '../features/auth/stores/useAuthStore'
import { Briefcase, MapPin, DollarSign, ArrowRight } from 'lucide-vue-next'
import { sentenceCase, formatSalaryRange } from '../features/jobs/utils/formatters'

const authStore = useAuthStore()
const { jobs, isListLoading, loadJobs } = usePublicJobs()

const featuredJobs = computed(() => jobs.value.slice(0, 4))
const dashboardPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate'
  }

  if (authStore.role === 'employer') {
    return '/employer/dashboard'
  }

  if (authStore.role === 'admin') {
    return '/admin'
  }

  return '/'
})

const jobsPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate/jobs'
  }
  return '/jobs'
})

function companyLabel(job: { employer?: { company_name?: string | null } | null }) {
  return job.employer?.company_name ?? 'Hiring company'
}

onMounted(async () => {
  await loadJobs({ per_page: 4 })
})
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 md:px-8 grid gap-10 py-10 lg:grid-cols-[minmax(0,0.95fr)_minmax(28rem,1fr)] lg:items-start lg:py-14">
    <div class="pt-2 lg:pt-10">
      <div class="mb-6 flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-800">
        Talent Board
      </div>
      <h1 class="max-w-3xl text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
        Find the right role without losing the hiring context.
      </h1>
      <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
        Search approved openings, review the essentials quickly, and sign in only when
        you need to manage candidate or employer workflows.
      </p>
      <div class="mt-8 flex flex-wrap items-center gap-4">
        <RouterLink
          :to="jobsPath"
          class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all active:scale-95"
        >
          Browse jobs
          <ArrowRight class="w-4 h-4" />
        </RouterLink>
        <template v-if="authStore.isAuthenticated">
          <RouterLink
            :to="dashboardPath"
            class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95"
          >
            Open dashboard
          </RouterLink>
        </template>
        <template v-else-if="!authStore.isSessionLoading">
          <RouterLink
            to="/auth/register"
            class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95"
          >
            Create account
          </RouterLink>
          <RouterLink
            to="/auth/login"
            class="inline-flex h-12 items-center justify-center rounded-lg border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all active:scale-95"
          >
            Sign in
          </RouterLink>
        </template>
      </div>

      <dl class="mt-12 grid max-w-xl grid-cols-3 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-200 transition-colors">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jobs</dt>
          <dd class="mt-2 text-3xl font-bold text-slate-900">100+</dd>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-200 transition-colors">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Roles</dt>
          <dd class="mt-2 text-3xl font-bold text-slate-900">4</dd>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-200 transition-colors">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mode</dt>
          <dd class="mt-2 text-3xl font-bold text-slate-900">SPA</dd>
        </div>
      </dl>
    </div>

    <aside class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="featured-jobs-title">
      <div class="mb-6 flex items-center justify-between gap-3">
        <div>
          <h2 id="featured-jobs-title" class="text-xl font-bold text-slate-900">
            Latest approved jobs
          </h2>
          <p class="mt-1 text-sm text-slate-500">A quick sample from the live job board.</p>
        </div>
        <RouterLink :to="jobsPath" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">
          View all
        </RouterLink>
      </div>

      <div v-if="isListLoading" class="grid gap-4" role="status" aria-live="polite">
        <div v-for="index in 4" :key="index" class="h-24 animate-pulse rounded-xl border border-slate-100 bg-slate-50" />
      </div>

      <ul v-else class="grid gap-4" aria-label="Featured job listings">
        <li v-for="job in featuredJobs" :key="job.id">
          <RouterLink
            :to="`/jobs/${job.id}`"
            class="block rounded-xl border border-slate-200 p-5 hover:border-emerald-300 hover:shadow-md transition-all group"
          >
            <div class="flex items-center gap-2 mb-2 text-xs font-semibold uppercase tracking-wider text-emerald-600">
              <span>{{ companyLabel(job) }}</span>
              <span class="text-slate-300">&bull;</span>
              <span>{{ job.category || 'General' }}</span>
            </div>
            
            <p class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors mb-3">{{ job.title }}</p>
            
            <div class="flex flex-wrap gap-2 text-xs font-medium text-slate-600">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100">
                <MapPin class="w-3.5 h-3.5 text-slate-400" />
                {{ job.location ?? 'Remote' }}
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
          </RouterLink>
        </li>
      </ul>
    </aside>
  </section>
</template>
