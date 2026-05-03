<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePublicJobs } from '../../composables/usePublicJobs'
import JobDetail from '../../features/jobs/components/JobDetail.vue'

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
</script>

<template>
  <section class="grid gap-5 py-8 sm:py-10">
    <RouterLink
      to="/jobs"
      class="inline-flex w-fit items-center text-sm font-semibold text-emerald-700 hover:text-emerald-800"
    >
      &larr; Back to jobs
    </RouterLink>

    <div v-if="isDetailLoading" class="grid gap-3" role="status" aria-live="polite" aria-busy="true">
      <div class="h-8 w-2/3 animate-pulse rounded bg-slate-200" />
      <div class="h-5 w-1/2 animate-pulse rounded bg-slate-200" />
      <div class="h-64 animate-pulse rounded bg-slate-200" />
    </div>

    <div
      v-else-if="formError"
      class="rounded-lg border border-dashed border-red-200 bg-red-50 px-5 py-12 text-center"
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
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-12 text-center"
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

    <JobDetail v-else :job="selectedJob" />
  </section>
</template>

