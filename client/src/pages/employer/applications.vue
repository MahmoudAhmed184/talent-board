<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import ApplicationStatusBadge from '../../components/ApplicationStatusBadge.vue'
import { useEmployerApplications } from '../../composables/useEmployerApplications'
import { useEcho, type ApplicationStatusChangedPayload } from '../../composables/useEcho'
import { useAuthStore } from '../../features/auth/stores/useAuthStore'

const decisionNote = ref('')
const authStore = useAuthStore()
const echo = useEcho()
const {
  list,
  selected,
  isLoading,
  isDetailLoading,
  isUpdatingStatus,
  isEmpty,
  formError,
  loadList,
  loadDetail,
  updateDecision,
  applyStatusUpdate,
} = useEmployerApplications()

const canDecide = computed(() => {
  const status = selected.value?.status
  return status === 'submitted' || status === 'under_review'
})

async function selectApplication(id: number) {
  await loadDetail(id)
}

async function decide(decision: 'accepted' | 'rejected') {
  if (!selected.value) {
    return
  }

  const ok = await updateDecision(selected.value.id, decision, decisionNote.value)
  if (ok) {
    decisionNote.value = ''
  }
}

onMounted(async () => {
  if (authStore.user?.id) {
    echo.subscribePrivate<ApplicationStatusChangedPayload>(
      `application-status.employer.${authStore.user.id}`,
      '.ApplicationStatusChanged',
      (payload) => applyStatusUpdate(payload),
    )
  }

  await loadList()
  const firstItem = list.value[0]
  if (firstItem) {
    await loadDetail(firstItem.id)
  }
})
</script>

<template>
  <section class="grid gap-4 lg:grid-cols-[22rem_1fr]">
    <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <header class="mb-3">
        <h1 class="text-lg font-semibold text-slate-950">Applications</h1>
        <p class="text-sm text-slate-600">
          Review candidate submissions for your job listings.
        </p>
      </header>

      <div v-if="isLoading" class="grid gap-2" role="status" aria-live="polite" aria-busy="true">
        <p class="text-sm text-slate-600">Loading applications...</p>
      </div>

      <div v-else-if="formError" class="grid gap-2 rounded-md border border-dashed border-red-200 bg-red-50 p-3" role="alert">
        <p class="text-sm text-red-700">{{ formError }}</p>
        <button
          type="button"
          class="inline-flex h-8 w-fit items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-100"
          @click="loadList"
        >
          Retry
        </button>
      </div>

      <div v-else-if="isEmpty" class="grid gap-2 rounded-md border border-dashed border-slate-300 p-3">
        <p class="text-sm text-slate-600">
          No applications yet. New submissions will show up here.
        </p>
      </div>

      <ul v-else class="grid gap-2" role="listbox" aria-label="Employer application list">
        <li v-for="item in list" :key="item.id" role="option" :aria-selected="selected?.id === item.id">
          <button
            type="button"
            class="w-full rounded-md border border-slate-200 px-3 py-2 text-left hover:border-cyan-300 hover:bg-cyan-50"
            :class="{ 'border-cyan-400 bg-cyan-50': selected?.id === item.id }"
            :aria-current="selected?.id === item.id ? 'true' : undefined"
            @click="selectApplication(item.id)"
          >
            <p class="truncate text-sm font-semibold text-slate-900">
              {{ item.candidate.name ?? 'Unnamed candidate' }}
            </p>
            <p class="truncate text-xs text-slate-600">
              {{ item.candidate.email ?? 'No email provided' }}
            </p>
            <div class="mt-2">
              <ApplicationStatusBadge :status="item.status" />
            </div>
          </button>
        </li>
      </ul>
    </aside>

    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <div v-if="isDetailLoading" role="status" aria-live="polite" aria-busy="true" class="text-sm text-slate-600">
        Loading selected application...
      </div>

      <div v-else-if="!selected" class="text-sm text-slate-600">
        Select an application to inspect candidate details.
      </div>

      <div v-else class="grid gap-5">
        <header class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="text-xl font-semibold text-slate-950">
              {{ selected.candidate.name ?? 'Unnamed candidate' }}
            </h2>
            <p class="text-sm text-slate-600">{{ selected.candidate.email ?? 'No email provided' }}</p>
          </div>
          <ApplicationStatusBadge :status="selected.status" />
        </header>

        <dl class="grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
          <div>
            <dt class="font-semibold text-slate-900">Job listing ID</dt>
            <dd>{{ selected.job_listing_id ?? 'Unknown' }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Submitted at</dt>
            <dd>{{ selected.submitted_at ?? 'Unknown' }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Contact email</dt>
            <dd>{{ selected.contact_email ?? 'Not provided' }}</dd>
          </div>
          <div>
            <dt class="font-semibold text-slate-900">Contact phone</dt>
            <dd>{{ selected.contact_phone ?? 'Not provided' }}</dd>
          </div>
        </dl>

        <section class="grid gap-2">
          <h3 class="text-sm font-semibold text-slate-900">Cover letter</h3>
          <p class="rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
            {{ selected.cover_letter ?? 'No cover letter provided.' }}
          </p>
        </section>

        <section class="grid gap-2">
          <h3 class="text-sm font-semibold text-slate-900">Resume file</h3>
          <p class="rounded-md border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
            {{ selected.resume.original_name ?? 'No resume reference attached.' }}
          </p>
        </section>

        <p
          v-if="formError"
          class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
          role="alert"
          aria-live="assertive"
        >
          {{ formError }}
        </p>

        <section class="grid gap-2" aria-label="Decision actions">
          <label for="decision-note" class="text-sm font-semibold text-slate-900">
            Decision note (optional)
          </label>
          <textarea
            id="decision-note"
            v-model="decisionNote"
            rows="3"
            class="rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-100 disabled:bg-slate-100"
            :disabled="!canDecide || isUpdatingStatus"
            maxlength="1000"
          />
          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
              :disabled="!canDecide || isUpdatingStatus"
              @click="decide('accepted')"
            >
              Accept
            </button>
            <button
              type="button"
              class="inline-flex h-10 items-center justify-center rounded-md bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-slate-300"
              :disabled="!canDecide || isUpdatingStatus"
              @click="decide('rejected')"
            >
              Reject
            </button>
          </div>
        </section>
      </div>
    </article>
  </section>
</template>
