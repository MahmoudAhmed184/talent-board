<script setup lang="ts">
import { onMounted, ref } from 'vue'
import Pagination from '../../../components/Pagination.vue'
import { useAdminJobModeration } from '../../../composables/useAdminJobModeration'

const rejectionNotes = ref<Record<number, string>>({})
const {
  activity,
  allJobs,
  allPaginationLinks,
  allPaginationMeta,
  formError,
  isLoading,
  isRefreshingActivity,
  isUpdating,
  pendingJobs,
  pendingPaginationLinks,
  pendingPaginationMeta,
  approveJob,
  loadActivity,
  loadAllJobs,
  loadPendingJobs,
  refreshAll,
  rejectJob,
} = useAdminJobModeration()

async function approve(id: number) {
  const ok = await approveJob(id)
  if (ok) {
    await refreshAll()
  }
}

async function reject(id: number) {
  const ok = await rejectJob(id, rejectionNotes.value[id] ?? '')
  if (ok) {
    rejectionNotes.value[id] = ''
    await refreshAll()
  }
}

onMounted(() => {
  refreshAll()
})

async function changePendingPage(page: number) {
  await Promise.all([loadPendingJobs(page), loadActivity()])
}

async function changeAllJobsPage(page: number) {
  await Promise.all([loadAllJobs(page), loadActivity()])
}
</script>

<template>
  <section class="grid gap-6">
    <header class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-violet-700">Admin moderation</p>
        <h1 class="text-3xl font-semibold text-slate-950">Jobs and platform activity</h1>
        <p class="mt-1 text-sm text-slate-600">
          Review pending jobs, moderate listings, and monitor platform activity from one place.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        :disabled="isLoading || isRefreshingActivity || isUpdating"
        @click="refreshAll"
      >
        Refresh
      </button>
    </header>

    <p
      v-if="formError"
      class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
      role="alert"
    >
      {{ formError }}
    </p>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Users</p>
        <p class="mt-2 text-3xl font-semibold text-slate-950">{{ activity?.totals.users ?? '—' }}</p>
      </article>
      <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jobs</p>
        <p class="mt-2 text-3xl font-semibold text-slate-950">{{ activity?.totals.jobs ?? '—' }}</p>
      </article>
      <article class="rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pending</p>
        <p class="mt-2 text-3xl font-semibold text-amber-900">{{ activity?.totals.jobs_pending ?? '—' }}</p>
      </article>
      <article class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Approved</p>
        <p class="mt-2 text-3xl font-semibold text-emerald-900">{{ activity?.totals.jobs_approved ?? '—' }}</p>
      </article>
      <article class="rounded-lg border border-rose-200 bg-rose-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Rejected</p>
        <p class="mt-2 text-3xl font-semibold text-rose-900">{{ activity?.totals.jobs_rejected ?? '—' }}</p>
      </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
      <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-slate-950">Pending moderation queue</h2>
            <p class="text-sm text-slate-600">Approve or reject jobs waiting for review.</p>
          </div>
          <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-800">
            {{ pendingJobs.length }} pending
          </span>
        </div>

        <div v-if="isLoading && pendingJobs.length === 0" class="mt-4 text-sm text-slate-600">
          Loading moderation queue...
        </div>

        <div
          v-else-if="pendingJobs.length === 0"
          class="mt-4 rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-600"
        >
          No pending jobs right now.
        </div>

        <ul v-else class="mt-4 grid gap-4">
          <li
            v-for="job in pendingJobs"
            :key="job.id"
            class="rounded-lg border border-slate-200 bg-slate-50 p-4"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 class="text-base font-semibold text-slate-950">{{ job.title }}</h3>
                <p class="mt-1 text-sm text-slate-600">
                  {{ job.employer?.company_name || job.employer?.name || 'Unknown employer' }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                  {{ job.category || 'No category' }} · {{ job.location || 'No location' }}
                </p>
              </div>
              <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                Pending
              </span>
            </div>

            <div class="mt-4 grid gap-2">
              <label :for="`reason-${job.id}`" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                Rejection reason
              </label>
              <textarea
                :id="`reason-${job.id}`"
                v-model="rejectionNotes[job.id]"
                rows="3"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-violet-600 focus:ring-2 focus:ring-violet-100"
                placeholder="Optional note for the employer"
              />
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 disabled:bg-slate-300"
                :disabled="isUpdating"
                @click="approve(job.id)"
              >
                Approve
              </button>
              <button
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md bg-rose-700 px-4 text-sm font-semibold text-white hover:bg-rose-800 disabled:bg-slate-300"
                :disabled="isUpdating"
                @click="reject(job.id)"
              >
                Reject
              </button>
            </div>
          </li>
        </ul>

        <Pagination
          :links="pendingPaginationLinks"
          :meta="pendingPaginationMeta"
          :disabled="isLoading || isUpdating"
          @page-change="changePendingPage"
        />
      </article>

      <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-950">Recent platform activity</h2>
        <p class="text-sm text-slate-600">A quick read on the newest users, jobs, and applications.</p>

        <div class="mt-5 grid gap-5">
          <section>
            <h3 class="text-sm font-semibold text-slate-900">Recent users</h3>
            <ul class="mt-2 grid gap-2 text-sm text-slate-700">
              <li v-for="user in activity?.recent_users ?? []" :key="user.id" class="rounded-md border border-slate-200 px-3 py-2">
                {{ user.name }} · {{ user.role }}
              </li>
            </ul>
          </section>

          <section>
            <h3 class="text-sm font-semibold text-slate-900">Recent applications</h3>
            <ul class="mt-2 grid gap-2 text-sm text-slate-700">
              <li
                v-for="application in activity?.recent_applications ?? []"
                :key="application.id"
                class="rounded-md border border-slate-200 px-3 py-2"
              >
                {{ application.job_listing?.title || 'Unknown job' }} · {{ application.status }}
              </li>
            </ul>
          </section>
        </div>
      </article>
    </section>

    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-slate-950">All moderated jobs</h2>
          <p class="text-sm text-slate-600">Recent decisions and listing state across the platform.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
          {{ allJobs.length }} jobs
        </span>
      </div>

      <div v-if="allJobs.length === 0" class="mt-4 text-sm text-slate-600">
        No jobs to show yet.
      </div>

      <div v-else class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
          <thead>
            <tr class="text-slate-500">
              <th class="py-3 pr-4 font-semibold">Title</th>
              <th class="py-3 pr-4 font-semibold">Employer</th>
              <th class="py-3 pr-4 font-semibold">Category</th>
              <th class="py-3 pr-4 font-semibold">Status</th>
              <th class="py-3 font-semibold">Published</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="job in allJobs" :key="job.id">
              <td class="py-3 pr-4 font-medium text-slate-950">{{ job.title }}</td>
              <td class="py-3 pr-4 text-slate-600">{{ job.employer?.company_name || job.employer?.name || '—' }}</td>
              <td class="py-3 pr-4 text-slate-600">{{ job.category || '—' }}</td>
              <td class="py-3 pr-4">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="{
                    'bg-amber-100 text-amber-800': job.approval_status === 'pending',
                    'bg-emerald-100 text-emerald-800': job.approval_status === 'approved',
                    'bg-rose-100 text-rose-800': job.approval_status === 'rejected',
                  }"
                >
                  {{ job.approval_status }}
                </span>
              </td>
              <td class="py-3 text-slate-600">{{ job.published_at || 'Not published' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        :links="allPaginationLinks"
        :meta="allPaginationMeta"
        :disabled="isLoading || isUpdating"
        @page-change="changeAllJobsPage"
      />
    </article>
  </section>
</template>
