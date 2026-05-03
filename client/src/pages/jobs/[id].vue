<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePublicJobs } from '../../composables/usePublicJobs'

const route = useRoute()
const { selectedJob, isDetailLoading, formError, loadJob } = usePublicJobs()

const jobId = computed(() => {
  const params = route.params as Record<string, string | string[] | undefined>
  const rawId = params.id

  if (Array.isArray(rawId)) {
    return rawId[0] ?? ''
  }

  return rawId ? String(rawId) : ''
})

async function load(): Promise<void> {
  if (!jobId.value) {
    return
  }

  await loadJob(jobId.value)
}

watch(jobId, (newId, oldId) => {
  if (newId && newId !== oldId) {
    load()
  }
})

onMounted(load)

function sentenceCase(value?: string | null): string {
  if (!value) {
    return 'Not specified'
  }

  return value
    .replaceAll('_', ' ')
    .replaceAll('-', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function salaryLabel(): string {
  const min = selectedJob.value?.salary_min
  const max = selectedJob.value?.salary_max
  const formatter = new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
    style: 'currency',
    currency: 'USD',
  })

  if (min && max) {
    return `${formatter.format(min)} - ${formatter.format(max)}`
  }

  if (min) {
    return `From ${formatter.format(min)}`
  }

  if (max) {
    return `Up to ${formatter.format(max)}`
  }

  return 'Salary not listed'
}

function dateLabel(value?: string | null): string {
  if (!value) {
    return 'Not specified'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(value))
}
</script>

<template>
  <section class="grid gap-5 py-8 sm:py-10">
    <RouterLink
      to="/jobs"
      class="inline-flex w-fit items-center text-sm font-semibold text-emerald-700 hover:text-emerald-800"
    >
      Back to jobs
    </RouterLink>

    <div v-if="isDetailLoading" class="grid gap-3" role="status" aria-live="polite" aria-busy="true">
      <div class="h-8 w-2/3 animate-pulse rounded bg-slate-200" />
      <div class="h-5 w-1/2 animate-pulse rounded bg-slate-200" />
      <div class="h-36 animate-pulse rounded bg-slate-200" />
      <p class="text-sm text-slate-600">Loading job details...</p>
    </div>

    <div
      v-else-if="formError"
      class="rounded-lg border border-dashed border-red-200 bg-red-50 px-5 py-8 text-center"
      role="alert"
    >
      <p class="text-base font-medium text-slate-900">Unable to load this job</p>
      <p class="mt-1 text-sm text-red-700">{{ formError }}</p>
      <button
        type="button"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        @click="load"
      >
        Retry
      </button>
    </div>

    <div
      v-else-if="!selectedJob"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-8 text-center"
    >
      <p class="text-base font-medium text-slate-900">Job not found</p>
      <p class="mt-1 text-sm text-slate-600">
        This listing is unavailable or no longer public.
      </p>
      <RouterLink
        to="/jobs"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
      >
        Back to jobs
      </RouterLink>
    </div>

    <article v-else class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <header class="border-b border-slate-200 pb-6">
          <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">
            {{ selectedJob.category ?? 'Job detail' }}
          </p>
          <h1 class="mt-2 text-3xl font-semibold leading-tight text-slate-950 sm:text-4xl">
            {{ selectedJob.title }}
          </h1>
          <p class="mt-3 text-base font-medium text-slate-700">
            {{ selectedJob.employer?.company_name ?? 'Hiring company' }}
          </p>
          <p class="mt-2 text-sm text-slate-600">
            {{ selectedJob.location ?? 'Location not specified' }} ·
            {{ sentenceCase(selectedJob.work_type) }} ·
            {{ sentenceCase(selectedJob.experience_level) }}
          </p>
        </header>

        <section class="mt-6 grid gap-3">
          <h2 class="text-lg font-semibold text-slate-900">Description</h2>
          <p class="whitespace-pre-line text-sm leading-7 text-slate-700">
            {{ selectedJob.description ?? 'No description provided yet.' }}
          </p>
        </section>

        <section v-if="selectedJob.responsibilities" class="mt-6 grid gap-3">
          <h2 class="text-lg font-semibold text-slate-900">Responsibilities</h2>
          <p class="whitespace-pre-line text-sm leading-7 text-slate-700">
            {{ selectedJob.responsibilities }}
          </p>
        </section>

        <section class="mt-6 grid gap-3">
          <h2 class="text-lg font-semibold text-slate-900">Qualifications</h2>
          <p class="whitespace-pre-line text-sm leading-7 text-slate-700">
            {{ selectedJob.qualifications ?? 'No qualification details provided yet.' }}
          </p>
        </section>
      </div>

      <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-slate-950">Listing summary</h2>
        <dl class="mt-4 grid gap-4 text-sm">
          <div>
            <dt class="font-semibold text-slate-900">Company</dt>
            <dd class="mt-1 text-slate-600">{{ selectedJob.employer?.company_name ?? 'Hiring company' }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Salary</dt>
            <dd class="mt-1 text-slate-600">{{ salaryLabel() }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Work type</dt>
            <dd class="mt-1 text-slate-600">{{ sentenceCase(selectedJob.work_type) }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Experience</dt>
            <dd class="mt-1 text-slate-600">{{ sentenceCase(selectedJob.experience_level) }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Published</dt>
            <dd class="mt-1 text-slate-600">{{ dateLabel(selectedJob.published_at) }}</dd>
          </div>
        </dl>
      </aside>
    </article>
  </section>
</template>
