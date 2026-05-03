<script setup lang="ts">
import { type PublicJobSummary } from '../../../composables/usePublicJobs'
import {
  sentenceCase,
  formatSalaryRange,
  descriptionSnippet,
  formatPostedDate,
} from '../utils/formatters'

const { job } = defineProps<{
  job: PublicJobSummary
}>()
</script>

<template>
  <article class="grid gap-4 p-5 md:grid-cols-[1fr_auto] md:items-start">
    <div class="min-w-0">
      <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
        <span>{{ job.employer?.company_name ?? 'Hiring company' }}</span>
        <span aria-hidden="true">/</span>
        <span>{{ job.category ?? 'General' }}</span>
      </div>
      <h2 class="mt-2 text-xl font-semibold text-slate-950">
        <RouterLink :to="`/jobs/${job.id}`" class="hover:text-emerald-700">
          {{ job.title }}
        </RouterLink>
      </h2>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        {{ descriptionSnippet(job.description) }}
      </p>

      <dl class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-slate-700">
        <div class="rounded-full bg-slate-100 px-3 py-1">
          {{ job.location ?? 'Remote' }}
        </div>
        <div class="rounded-full bg-slate-100 px-3 py-1">
          {{ sentenceCase(job.work_type) }}
        </div>
        <div class="rounded-full bg-slate-100 px-3 py-1">
          {{ sentenceCase(job.experience_level) }}
        </div>
        <div class="rounded-full bg-slate-100 px-3 py-1">
          {{ formatSalaryRange(job.salary_min, job.salary_max) }}
        </div>
      </dl>
    </div>

    <div class="flex flex-row items-center justify-between gap-3 md:flex-col md:items-end">
      <p class="text-sm text-slate-500">{{ formatPostedDate(job.published_at) }}</p>
      <RouterLink
        :to="`/jobs/${job.id}`"
        class="inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
      >
        View details
      </RouterLink>
    </div>
  </article>
</template>
