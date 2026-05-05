<script setup lang="ts">
import { computed, onMounted } from 'vue'
import Pagination from '../../components/Pagination.vue'
import { useEmployerJobsStore, type ApprovalStatus } from '../../stores/useEmployerJobsStore'

const jobsStore = useEmployerJobsStore()

const statusClasses: Record<ApprovalStatus, string> = {
  pending: 'border-amber-200 bg-amber-50 text-amber-800',
  approved: 'border-emerald-200 bg-emerald-50 text-emerald-800',
  rejected: 'border-red-200 bg-red-50 text-red-800',
}

const totalJobs = computed(() => jobsStore.jobs.length)
const approvedJobs = computed(
  () => jobsStore.jobs.filter((job) => job.approval_status === 'approved').length,
)
const pendingJobs = computed(
  () => jobsStore.jobs.filter((job) => job.approval_status === 'pending').length,
)

async function removeJob(jobId: number) {
  await jobsStore.deleteJob(jobId)
}

async function changePage(page: number) {
  await jobsStore.fetchJobs(page)
}

onMounted(async () => {
  await jobsStore.fetchJobs()
})
</script>

<template>
  <section class="grid gap-5">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-slate-950">Employer dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">
          Track owned listings, moderation state, and draft updates.
        </p>
      </div>
      <RouterLink
        to="/employer/jobs/create"
        class="inline-flex h-10 w-fit items-center justify-center rounded-md bg-cyan-700 px-4 text-sm font-semibold text-white hover:bg-cyan-800"
      >
        Create job
      </RouterLink>
    </header>

    <dl class="grid gap-3 sm:grid-cols-3">
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <dt class="text-sm font-medium text-slate-600">Total listings</dt>
        <dd class="mt-2 text-2xl font-semibold text-slate-950">{{ totalJobs }}</dd>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <dt class="text-sm font-medium text-slate-600">Approved</dt>
        <dd class="mt-2 text-2xl font-semibold text-emerald-700">{{ approvedJobs }}</dd>
      </div>
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <dt class="text-sm font-medium text-slate-600">Pending review</dt>
        <dd class="mt-2 text-2xl font-semibold text-amber-700">{{ pendingJobs }}</dd>
      </div>
    </dl>

    <p
      v-if="jobsStore.formError"
      class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      role="alert"
      aria-live="assertive"
    >
      {{ jobsStore.formError }}
    </p>

    <div v-if="jobsStore.isLoading" class="grid gap-3" role="status" aria-live="polite" aria-busy="true">
      <div
        v-for="index in 3"
        :key="index"
        class="h-24 animate-pulse rounded-lg border border-slate-200 bg-white"
      />
      <p class="text-sm text-slate-600">Loading owned listings...</p>
    </div>

    <div
      v-else-if="jobsStore.isEmpty"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-8 text-center"
    >
      <p class="font-medium text-slate-950">No employer listings yet</p>
      <p class="mt-1 text-sm text-slate-600">Create a job to submit it for moderation.</p>
      <RouterLink
        to="/employer/jobs/create"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md bg-cyan-700 px-4 text-sm font-semibold text-white hover:bg-cyan-800"
      >
        Create job
      </RouterLink>
    </div>

    <ul v-else class="grid gap-3" aria-label="Owned job listings">
      <li
        v-for="job in jobsStore.jobs"
        :key="job.id"
        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
      >
        <article class="grid gap-3 md:grid-cols-[1fr_auto] md:items-start">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <h2 class="text-lg font-semibold text-slate-950">{{ job.title }}</h2>
              <span
                class="rounded-full border px-2 py-0.5 text-xs font-semibold capitalize"
                :class="statusClasses[job.approval_status]"
              >
                {{ job.approval_status }}
              </span>
            </div>
            <p class="mt-1 text-sm text-slate-600">
              {{ job.location }} · {{ job.work_type }} · {{ job.experience_level }}
            </p>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-700">
              {{ job.description }}
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <RouterLink
              :to="`/employer/jobs/${job.id}/edit`"
              class="inline-flex h-9 items-center justify-center rounded-md border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100"
            >
              Edit
            </RouterLink>
            <button
              type="button"
              class="inline-flex h-9 items-center justify-center rounded-md border border-red-200 px-3 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="jobsStore.isDeleting"
              @click="removeJob(job.id)"
            >
              Remove
            </button>
          </div>
        </article>
      </li>
    </ul>

    <Pagination
      :links="jobsStore.paginationLinks"
      :meta="jobsStore.paginationMeta"
      :disabled="jobsStore.isLoading"
      @page-change="changePage"
    />
  </section>
</template>
