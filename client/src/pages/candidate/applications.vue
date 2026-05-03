<script setup lang="ts">
import { onMounted, ref } from 'vue'
import ApplicationStatusBadge from '../../components/ApplicationStatusBadge.vue'
import { useEcho, type ApplicationStatusChangedPayload } from '../../composables/useEcho'
import { useAuthStore } from '../../features/auth/stores/useAuthStore'

const authStore = useAuthStore()
const echo = useEcho()
const liveUpdates = ref<ApplicationStatusChangedPayload[]>([])

onMounted(() => {
  if (!authStore.user?.id) {
    return
  }

  echo.subscribePrivate<ApplicationStatusChangedPayload>(
    `application-status.candidate.${authStore.user.id}`,
    '.ApplicationStatusChanged',
    (payload) => {
      liveUpdates.value = [
        payload,
        ...liveUpdates.value.filter((item) => item.application_id !== payload.application_id),
      ]
    },
  )
})
</script>

<template>
  <section class="grid gap-5">
    <header>
      <h1 class="text-2xl font-semibold text-slate-950">Applications</h1>
      <p class="mt-1 text-sm text-slate-600">
        Recent application status changes update here as they are broadcast.
      </p>
    </header>

    <div
      v-if="liveUpdates.length === 0"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-8 text-center"
    >
      <p class="font-medium text-slate-950">No live status updates yet</p>
      <p class="mt-1 text-sm text-slate-600">
        Application decisions will appear without refreshing this page.
      </p>
    </div>

    <ul v-else class="grid gap-3" aria-label="Live application status updates">
      <li
        v-for="update in liveUpdates"
        :key="update.application_id"
        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
      >
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold text-slate-950">
              Application #{{ update.application_id }}
            </p>
            <p class="mt-1 text-sm text-slate-600">
              Updated {{ update.changed_at }}
            </p>
          </div>
          <ApplicationStatusBadge :status="update.status" />
        </div>
      </li>
    </ul>
  </section>
</template>
