<script setup lang="ts">
import { computed } from 'vue'

type ApplicationStatus = 'submitted' | 'under_review' | 'accepted' | 'rejected' | 'cancelled'

const { status } = defineProps<{
  status: ApplicationStatus
}>()

const badgeLabel = computed(() => {
  if (status === 'under_review') {
    return 'Under review'
  }

  return status.charAt(0).toUpperCase() + status.slice(1)
})

const badgeClass = computed(() => {
  switch (status) {
    case 'accepted':
      return 'bg-emerald-100 text-emerald-800 ring-emerald-600/20'
    case 'rejected':
      return 'bg-red-100 text-red-800 ring-red-600/20'
    case 'cancelled':
      return 'bg-slate-200 text-slate-700 ring-slate-500/20'
    case 'under_review':
      return 'bg-amber-100 text-amber-800 ring-amber-600/20'
    default:
      return 'bg-blue-100 text-blue-800 ring-blue-600/20'
  }
})
</script>

<template>
  <span
    role="status"
    :aria-label="`Application status: ${badgeLabel}`"
    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
    :class="badgeClass"
  >
    {{ badgeLabel }}
  </span>
</template>
