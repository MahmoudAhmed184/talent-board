<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { usePublicJobs } from '../composables/usePublicJobs'
import { useAuthStore } from '../features/auth/stores/useAuthStore'

const authStore = useAuthStore()
const { jobs, isListLoading, loadJobs } = usePublicJobs()

const featuredJobs = computed(() => jobs.value.slice(0, 4))
const dashboardPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate/applications'
  }

  if (authStore.role === 'employer') {
    return '/employer/dashboard'
  }

  if (authStore.role === 'admin') {
    return '/admin'
  }

  return '/'
})

function companyLabel(job: { employer?: { company_name?: string | null } | null }) {
  return job.employer?.company_name ?? 'Hiring company'
}

onMounted(async () => {
  await loadJobs({ per_page: 4 })
})
</script>

<template>
  <section class="grid gap-10 py-10 lg:grid-cols-[minmax(0,0.95fr)_minmax(28rem,1fr)] lg:items-start lg:py-14">
    <div class="pt-2 lg:pt-10">
      <div class="mb-6 flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-800">
        Talent Board
      </div>
      <h1 class="max-w-3xl text-4xl font-semibold leading-tight tracking-normal text-slate-950 sm:text-5xl lg:text-6xl">
        Find the right role without losing the hiring context.
      </h1>
      <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
        Search approved openings, review the essentials quickly, and sign in only when
        you need to manage candidate or employer workflows.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <RouterLink
          to="/jobs"
          class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"
        >
          Browse jobs
        </RouterLink>
        <template v-if="authStore.isAuthenticated">
          <RouterLink
            :to="dashboardPath"
            class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          >
            Open dashboard
          </RouterLink>
        </template>
        <template v-else-if="!authStore.isSessionLoading">
          <RouterLink
            to="/auth/register"
            class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          >
            Create account
          </RouterLink>
          <RouterLink
            to="/auth/login"
            class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          >
            Sign in
          </RouterLink>
        </template>
      </div>

      <dl class="mt-10 grid max-w-xl grid-cols-3 gap-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jobs</dt>
          <dd class="mt-2 text-2xl font-semibold text-slate-950">100+</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Roles</dt>
          <dd class="mt-2 text-2xl font-semibold text-slate-950">4</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mode</dt>
          <dd class="mt-2 text-2xl font-semibold text-slate-950">SPA</dd>
        </div>
      </dl>
    </div>

    <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="featured-jobs-title">
      <div class="mb-4 flex items-center justify-between gap-3">
        <div>
          <h2 id="featured-jobs-title" class="text-lg font-semibold text-slate-950">
            Latest approved jobs
          </h2>
          <p class="mt-1 text-sm text-slate-600">A quick sample from the live job board.</p>
        </div>
        <RouterLink to="/jobs" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
          View all
        </RouterLink>
      </div>

      <div v-if="isListLoading" class="grid gap-3" role="status" aria-live="polite">
        <div v-for="index in 4" :key="index" class="h-20 animate-pulse rounded-md bg-slate-100" />
      </div>

      <ul v-else class="grid gap-3" aria-label="Featured job listings">
        <li v-for="job in featuredJobs" :key="job.id">
          <RouterLink
            :to="`/jobs/${job.id}`"
            class="block rounded-md border border-slate-200 p-4 hover:border-emerald-200 hover:bg-emerald-50/60"
          >
            <p class="text-sm font-semibold text-slate-950">{{ job.title }}</p>
            <p class="mt-1 text-sm text-slate-600">
              {{ companyLabel(job) }} · {{ job.location ?? 'Remote' }}
            </p>
          </RouterLink>
        </li>
      </ul>
    </aside>
  </section>
</template>
