<script setup lang="ts">
import { useRouter } from 'vue-router'
import JobForm from '../../../components/employer/JobForm.vue'
import type { EmployerJobPayload } from '../../../composables/useEmployerJobForm'
import { useEmployerJobsStore } from '../../../stores/useEmployerJobsStore'
import { useToast } from '../../../composables/useToast'

const router = useRouter()
const jobsStore = useEmployerJobsStore()
const { error: showError } = useToast()

async function submitJob(payload: EmployerJobPayload): Promise<void> {
  try {
    await jobsStore.createJob(payload)
    await router.push('/employer/dashboard')
  } catch {
    // 422 validation errors are already mapped by the store.
    // Other errors (500, network, etc.) surface via the store's formError.
    showError('Failed to create job listing. Please try again.')
  }
}
</script>

<template>
  <JobForm submit-label="Submit for review" :submit-handler="submitJob" />
</template>
