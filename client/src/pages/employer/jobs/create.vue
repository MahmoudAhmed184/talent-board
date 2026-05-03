<script setup lang="ts">
import { useRouter } from 'vue-router'
import JobForm from '../../../components/employer/JobForm.vue'
import type { EmployerJobPayload } from '../../../composables/useEmployerJobForm'
import { useEmployerJobsStore } from '../../../stores/useEmployerJobsStore'

const router = useRouter()
const jobsStore = useEmployerJobsStore()

async function submitJob(payload: EmployerJobPayload): Promise<void> {
  await jobsStore.createJob(payload)
  await router.push('/employer/dashboard')
}
</script>

<template>
  <JobForm submit-label="Submit for review" :submit-handler="submitJob" />
</template>
