<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { usePublicJobs } from '../../composables/usePublicJobs'

const query = ref('')
const isInitialized = ref(false)
const { jobs, isListLoading, formError, loadJobs } = usePublicJobs()

const isEmpty = computed(
  () => isInitialized.value && !isListLoading.value && jobs.value.length === 0,
)

async function search(): Promise<void> {
  await loadJobs(query.value)
}

onMounted(async () => {
  await loadJobs()
  isInitialized.value = true
})
</script>

<template>
  <section class="py-8 sm:py-10">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-slate-950 sm:text-3xl">Browse jobs</h1>
        <p class="mt-1 text-sm text-slate-600">
          Search approved openings and review key listing details.
        </p>
      </div>

      <form class="flex w-full max-w-xl flex-col gap-2 sm:flex-row" @submit.prevent="search">
        <label for="jobs-search" class="sr-only">Search jobs</label>
        <input
          id="jobs-search"
          v-model="query"
          type="search"
          placeholder="Search by title or keyword"
          class="h-11 flex-1 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        >
        <button
          type="submit"
          class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
        >
          Search
        </button>
      </form>
    </header>

    <p
      v-if="formError"
      class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      role="alert"
      aria-live="assertive"
    >
      {{ formError }}
    </p>

    <div v-if="isListLoading" class="grid gap-3" role="status" aria-live="polite" aria-busy="true">
      <div
        v-for="index in 3"
        :key="index"
        class="h-28 animate-pulse rounded-lg border border-slate-200 bg-white"
        aria-hidden="true"
      />
      <p class="text-sm text-slate-600">Loading jobs...</p>
    </div>

    <div
      v-else-if="isEmpty"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-8 text-center"
    >
      <p class="text-base font-medium text-slate-900">No jobs found</p>
      <p class="mt-1 text-sm text-slate-600">
        Try adjusting your search and run it again.
      </p>
      <button
        type="button"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        @click="search"
      >
        Retry search
      </button>
    </div>

    <ul v-else class="grid gap-4" aria-label="Public job search results">
      <li
        v-for="job in jobs"
        :key="job.id"
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
      >
        <article class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-start">
          <div>
            <h2 class="text-lg font-semibold text-slate-950">
              <RouterLink :to="`/jobs/${job.id}`" class="hover:text-emerald-700">
                {{ job.title }}
              </RouterLink>
            </h2>
            <p class="mt-1 text-sm text-slate-600">
              {{ job.location ?? 'Location not specified' }} ·
              {{ job.work_type ?? 'Work type TBD' }}
            </p>
          </div>
          <RouterLink
            :to="`/jobs/${job.id}`"
            class="inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
          >
            View details
          </RouterLink>
        </article>
      </li>
    </ul>
  </section>
</template>
