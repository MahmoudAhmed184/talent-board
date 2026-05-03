<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import JobForm from '../../../../components/employer/JobForm.vue'
import type { EmployerJobPayload } from '../../../../composables/useEmployerJobForm'
import {
  toEmployerJobFormData,
  useEmployerJobsStore,
} from '../../../../stores/useEmployerJobsStore'

const route = useRoute()
const router = useRouter()
const jobsStore = useEmployerJobsStore()

const jobId = computed(() => {
  const params = route.params as Record<string, string | string[] | undefined>
  const rawId = params.id
  return Array.isArray(rawId) ? rawId[0] : String(rawId ?? '')
})
const initialValues = computed(() =>
  jobsStore.currentJob ? toEmployerJobFormData(jobsStore.currentJob) : {},
)

async function loadJob() {
  if (jobId.value) {
    await jobsStore.fetchJob(jobId.value)
  }
}

async function submitJob(payload: EmployerJobPayload): Promise<void> {
  await jobsStore.updateJob(jobId.value, payload)
  await router.push('/employer/dashboard')
}

watch(jobId, async (newId, oldId) => {
  if (newId && newId !== oldId) {
    await loadJob()
  }
})

onMounted(loadJob)
</script>

<template>
  <section class="grid gap-4">
    <RouterLink
      to="/employer/dashboard"
      class="inline-flex w-fit items-center text-sm font-semibold text-cyan-700 hover:text-cyan-800"
    >
      Back to dashboard
    </RouterLink>

    <div v-if="jobsStore.isLoading" class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600">
      Loading job listing...
    </div>

    <div
      v-else-if="jobsStore.formError && !jobsStore.currentJob"
      class="rounded-lg border border-red-200 bg-red-50 p-6"
      role="alert"
    >
      <p class="text-sm text-red-700">{{ jobsStore.formError }}</p>
      <button
        type="button"
        class="mt-3 inline-flex h-9 items-center justify-center rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        @click="loadJob"
      >
        Retry
      </button>
    </div>

    <JobForm
      v-else
      mode="edit"
      submit-label="Save changes"
      :initial-values="initialValues"
      :submit-handler="submitJob"
    />
  </section>
</template>
