<script setup lang="ts">
import JobForm from './components/employer/JobForm.vue';
import type { EmployerJobPayload } from './composables/useEmployerJobForm';

const handleSubmit = (payload: EmployerJobPayload): void => {
  // This placeholder keeps the shared form fully reusable while backend
  // contracts are still being finalized in parallel slices.
  console.info('Employer job payload', payload);
};
</script>

<template>
  <main class="app-shell">
    <JobForm mode="create" submit-label="Save draft" @submit="handleSubmit" />
  </main>
</template>
