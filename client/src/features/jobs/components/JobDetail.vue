<script setup lang="ts">
import { type PublicJobDetail } from '../../../composables/usePublicJobs'
import { sentenceCase, formatSalaryRange } from '../utils/formatters'

const { job } = defineProps<{
  job: PublicJobDetail
}>()

const emit = defineEmits<{
  (e: 'apply'): void
}>()
</script>

<template>
  <div class="grid gap-8">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
        <div>
          <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-emerald-700">
            <span>{{ job.category ?? 'Job detail' }}</span>
            <span aria-hidden="true">•</span>
            <span>{{ job.location ?? 'Remote' }}</span>
          </div>
          <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ job.title }}</h1>
          <p class="mt-2 text-lg text-slate-600">
            {{ job.employer?.company_name ?? 'Hiring company' }}
          </p>
        </div>

        <div class="flex flex-col gap-3">
          <div class="rounded-lg bg-emerald-50 p-4 text-center">
            <p class="text-xs font-semibold uppercase text-emerald-800">Salary Range</p>
            <p class="mt-1 text-lg font-bold text-emerald-950">
              {{ formatSalaryRange(job.salary_min, job.salary_max) }}
            </p>
          </div>
        </div>
      </div>

      <div class="mt-8 grid gap-8 md:grid-cols-3">
        <div class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase text-slate-500">Work Type</p>
          <p class="mt-1 font-medium text-slate-900">{{ sentenceCase(job.work_type) }}</p>
        </div>
        <div class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase text-slate-500">Experience</p>
          <p class="mt-1 font-medium text-slate-900">{{ sentenceCase(job.experience_level) }}</p>
        </div>
        <div class="rounded-md bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase text-slate-500">Category</p>
          <p class="mt-1 font-medium text-slate-900">{{ job.category ?? 'General' }}</p>
        </div>
      </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-[1fr_300px]">
      <div class="flex flex-col gap-8">
        <section v-if="job.description">
          <h2 class="text-xl font-bold text-slate-950">Description</h2>
          <div class="prose prose-slate mt-4 max-w-none whitespace-pre-line text-slate-600">
            {{ job.description }}
          </div>
        </section>

        <section v-if="job.responsibilities">
          <h2 class="text-xl font-bold text-slate-950">Responsibilities</h2>
          <div class="prose prose-slate mt-4 max-w-none whitespace-pre-line text-slate-600">
            {{ job.responsibilities }}
          </div>
        </section>

        <section v-if="job.qualifications">
          <h2 class="text-xl font-bold text-slate-950">Qualifications</h2>
          <div class="prose prose-slate mt-4 max-w-none whitespace-pre-line text-slate-600">
            {{ job.qualifications }}
          </div>
        </section>
      </div>

      <aside class="flex flex-col gap-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-bold text-slate-950">About the Employer</h3>
          <p class="mt-4 text-sm text-slate-600">
            {{ job.employer?.company_name ?? 'This company' }} is looking for a
            talented {{ job.title }} to join their team in
            {{ job.location ?? 'a remote capacity' }}.
          </p>
          <button
            type="button"
            class="mt-6 w-full rounded-md bg-emerald-700 py-3 text-sm font-semibold text-white hover:bg-emerald-800"
            @click="emit('apply')"
          >
            Apply Now
          </button>
        </div>
      </aside>
    </div>
  </div>
</template>
