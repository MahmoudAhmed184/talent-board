<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useCandidateApplicationsStore } from '../../features/candidate/stores/useCandidateApplicationsStore'
import { FileText, CheckCircle, XCircle, Clock, ArrowRight } from 'lucide-vue-next'
import { format } from 'date-fns'

const applicationsStore = useCandidateApplicationsStore()

onMounted(async () => {
  await applicationsStore.loadPage(1)
})

const applications = computed(() => applicationsStore.applications || [])
const recentApplications = computed(() => applications.value.slice(0, 5))

// Calculate stats based on fetched applications
// In a real app, this might be an endpoint, but we use the fetched page/list for demo.
const stats = computed(() => {
  const total = applicationsStore.meta?.total || applications.value.length
  
  // Since we might only have page 1, true counts would need API support.
  // For the UI, we'll calculate from the current items, or if we had real stats from API.
  const pending = applications.value.filter(a => ['submitted', 'under_review'].includes(a.status)).length
  const accepted = applications.value.filter(a => a.status === 'accepted').length
  const rejected = applications.value.filter(a => a.status === 'rejected').length

  return [
    { name: 'Total Applications', value: total, icon: FileText, color: 'text-blue-600', bg: 'bg-blue-100' },
    { name: 'Accepted', value: accepted, icon: CheckCircle, color: 'text-emerald-600', bg: 'bg-emerald-100' },
    { name: 'Rejected', value: rejected, icon: XCircle, color: 'text-red-600', bg: 'bg-red-100' },
    { name: 'Pending Review', value: pending, icon: Clock, color: 'text-yellow-600', bg: 'bg-yellow-100' },
  ]
})

function getStatusColor(status: string) {
  switch (status) {
    case 'accepted': return 'bg-emerald-100 text-emerald-800 border-emerald-200'
    case 'rejected': return 'bg-red-100 text-red-800 border-red-200'
    case 'submitted':
    case 'under_review':
    case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200'
    default: return 'bg-slate-100 text-slate-800 border-slate-200'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dashboard Overview</h1>
      <p class="mt-1 text-sm text-slate-500">Welcome back. Here's a summary of your job applications.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in stats" :key="stat.name" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-500">{{ stat.name }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ stat.value }}</p>
          </div>
          <div :class="[stat.bg, stat.color, 'p-3 rounded-lg flex items-center justify-center']">
            <component :is="stat.icon" class="w-6 h-6" />
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Applications -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-900">Recent Applications</h2>
        <RouterLink to="/candidate/applications" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
          View all
          <ArrowRight class="w-4 h-4" />
        </RouterLink>
      </div>

      <div v-if="applicationsStore.isFetching && !applications.length" class="p-6 text-center text-slate-500">
        Loading...
      </div>
      <div v-else-if="!recentApplications.length" class="p-6 text-center text-slate-500">
        You haven't applied to any jobs yet.
      </div>
      <div v-else class="divide-y divide-slate-100">
        <div v-for="app in recentApplications" :key="app.id" class="p-6 hover:bg-slate-50 transition-colors flex items-center justify-between">
          <div class="flex flex-col gap-1">
            <h3 class="text-sm font-semibold text-slate-900">{{ app.job_listing?.title || 'Unknown Job' }}</h3>
            <p class="text-sm text-slate-500">{{ app.job_listing?.employer?.company_name || 'Unknown Company' }} &bull; Applied {{ format(new Date(app.submitted_at), 'MMM d, yyyy') }}</p>
          </div>
          <div>
            <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize transition-colors focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2', getStatusColor(app.status)]">
              {{ app.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
