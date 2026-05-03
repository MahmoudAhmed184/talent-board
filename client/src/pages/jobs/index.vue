<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Pagination from '../../components/Pagination.vue'
import { usePublicJobs, type PublicJobSummary } from '../../composables/usePublicJobs'

const perPage = 10
const route = useRoute()
const router = useRouter()
const filters = reactive({
  q: '',
  location: '',
  category: '',
  work_type: '',
  experience_level: '',
  date_posted: '',
})
const isInitialized = ref(false)
const { jobs, paginationLinks, paginationMeta, isListLoading, formError, loadJobs } =
  usePublicJobs()
const filterKeys = [
  'q',
  'location',
  'category',
  'work_type',
  'experience_level',
  'date_posted',
] as const

const isEmpty = computed(
  () => isInitialized.value && !isListLoading.value && jobs.value.length === 0,
)
const activeFilterCount = computed(
  () => Object.values(filters).filter((value) => value.trim() !== '').length,
)
const resultSummary = computed(() => {
  if (!isInitialized.value) {
    return 'Loading listings'
  }

  if (paginationMeta.value.total === 0) {
    return 'No listings'
  }

  return `${paginationMeta.value.from ?? 0}-${paginationMeta.value.to ?? 0} of ${paginationMeta.value.total} listings`
})

function filterPayload(page = 1) {
  return {
    ...filters,
    page,
    per_page: perPage,
  }
}

function queryValue(value: unknown): string {
  if (typeof value === 'string') {
    return value
  }

  return Array.isArray(value) && typeof value[0] === 'string' ? value[0] : ''
}

function pageFromQuery(): number {
  const page = Number(queryValue(route.query.page))

  return Number.isInteger(page) && page > 0 ? page : 1
}

function hydrateFiltersFromQuery(): void {
  filterKeys.forEach((key) => {
    filters[key] = queryValue(route.query[key])
  })
}

function queryForPage(page: number): Record<string, string> {
  const query: Record<string, string> = {}

  filterKeys.forEach((key) => {
    const value = filters[key].trim()

    if (value !== '') {
      query[key] = value
    }
  })

  if (page > 1) {
    query.page = String(page)
  }

  return query
}

async function search(page = 1): Promise<void> {
  await loadJobs(filterPayload(page))
  await router.replace({ path: '/jobs', query: queryForPage(page) })
}

async function resetFilters(): Promise<void> {
  Object.assign(filters, {
    q: '',
    location: '',
    category: '',
    work_type: '',
    experience_level: '',
    date_posted: '',
  })
  await search()
}

async function changePage(page: number): Promise<void> {
  await search(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function companyLabel(job: PublicJobSummary): string {
  return job.employer?.company_name ?? 'Hiring company'
}

function sentenceCase(value?: string | null): string {
  if (!value) {
    return 'Not specified'
  }

  return value
    .replaceAll('_', ' ')
    .replaceAll('-', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function salaryLabel(job: PublicJobSummary): string {
  const min = job.salary_min
  const max = job.salary_max
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

function descriptionSnippet(job: PublicJobSummary): string {
  const description = job.description?.replace(/\s+/g, ' ').trim()

  if (!description) {
    return 'Open role with details available on the listing page.'
  }

  return description.length > 260 ? `${description.slice(0, 260)}...` : description
}

function postedLabel(value?: string | null): string {
  if (!value) {
    return 'Posted recently'
  }

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(new Date(value))
}

onMounted(async () => {
  hydrateFiltersFromQuery()
  await loadJobs(filterPayload(pageFromQuery()))
  isInitialized.value = true
})
</script>

<template>
  <section class="grid gap-6 py-8 sm:py-10">
    <header class="flex flex-col gap-2">
      <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
        Public jobs
      </p>
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-slate-950 sm:text-4xl">Browse jobs</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Search approved openings, compare work mode and salary, then open the full
            listing when a role looks relevant.
          </p>
        </div>
        <p class="text-sm font-medium text-slate-600">
          {{ resultSummary }}
        </p>
      </div>
    </header>

    <form class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5" @submit.prevent="search()">
      <div class="grid gap-3 lg:grid-cols-[1fr_auto]">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1.4fr)_minmax(10rem,0.8fr)_minmax(10rem,0.8fr)]">
          <label for="jobs-search" class="sr-only">Search jobs</label>
          <input
            id="jobs-search"
            v-model="filters.q"
            type="search"
            placeholder="Search by title or keyword"
            class="h-11 min-w-0 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
          <label class="grid gap-1">
            <span class="sr-only">Location</span>
            <input
              v-model="filters.location"
              type="text"
              placeholder="Location"
              class="h-11 min-w-0 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </label>
          <label class="grid gap-1">
            <span class="sr-only">Category</span>
            <input
              v-model="filters.category"
              type="text"
              placeholder="Category"
              class="h-11 min-w-0 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </label>
        </div>
        <button
          type="submit"
          class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"
        >
          Search
        </button>
      </div>

      <div class="mt-3 grid gap-3 md:grid-cols-[repeat(3,minmax(0,1fr))_auto]">
        <label class="grid gap-1">
          <span class="sr-only">Work type</span>
          <select
            v-model="filters.work_type"
            class="h-10 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option value="">Any work type</option>
            <option value="remote">Remote</option>
            <option value="on-site">On-site</option>
            <option value="hybrid">Hybrid</option>
          </select>
        </label>
        <label class="grid gap-1">
          <span class="sr-only">Experience level</span>
          <select
            v-model="filters.experience_level"
            class="h-10 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option value="">Any level</option>
            <option value="junior">Junior</option>
            <option value="mid">Mid</option>
            <option value="senior">Senior</option>
            <option value="lead">Lead</option>
          </select>
        </label>
        <label class="grid gap-1">
          <span class="sr-only">Date posted</span>
          <select
            v-model="filters.date_posted"
            class="h-10 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option value="">Any date</option>
            <option value="24h">Past 24 hours</option>
            <option value="7d">Past week</option>
            <option value="30d">Past month</option>
          </select>
        </label>
        <button
          type="button"
          class="inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
          :disabled="activeFilterCount === 0"
          @click="resetFilters"
        >
          Clear
        </button>
      </div>
    </form>

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
        class="h-36 animate-pulse rounded-lg border border-slate-200 bg-white"
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
        @click="resetFilters"
      >
        Clear filters
      </button>
    </div>

    <div v-else class="grid gap-4">
      <ul class="grid gap-4" aria-label="Public job search results">
        <li
          v-for="job in jobs"
          :key="job.id"
          class="rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-emerald-200 hover:shadow-md"
        >
          <article class="grid gap-4 p-5 md:grid-cols-[1fr_auto] md:items-start">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                <span>{{ companyLabel(job) }}</span>
                <span aria-hidden="true">/</span>
                <span>{{ job.category ?? 'General' }}</span>
              </div>
              <h2 class="mt-2 text-xl font-semibold text-slate-950">
                <RouterLink :to="`/jobs/${job.id}`" class="hover:text-emerald-700">
                  {{ job.title }}
                </RouterLink>
              </h2>
              <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                {{ descriptionSnippet(job) }}
              </p>

              <dl class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-slate-700">
                <div class="rounded-full bg-slate-100 px-3 py-1">
                  <dt class="sr-only">Location</dt>
                  <dd>{{ job.location ?? 'Location not specified' }}</dd>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1">
                  <dt class="sr-only">Work type</dt>
                  <dd>{{ sentenceCase(job.work_type) }}</dd>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1">
                  <dt class="sr-only">Experience</dt>
                  <dd>{{ sentenceCase(job.experience_level) }}</dd>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1">
                  <dt class="sr-only">Salary</dt>
                  <dd>{{ salaryLabel(job) }}</dd>
                </div>
              </dl>
            </div>

            <div class="flex flex-row items-center justify-between gap-3 md:flex-col md:items-end">
              <p class="text-sm text-slate-500">{{ postedLabel(job.published_at) }}</p>
              <RouterLink
                :to="`/jobs/${job.id}`"
                class="inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
              >
                View details
              </RouterLink>
            </div>
          </article>
        </li>
      </ul>

      <Pagination
        :links="paginationLinks"
        :meta="paginationMeta"
        :disabled="isListLoading"
        @page-change="changePage"
      />
    </div>
  </section>
</template>
