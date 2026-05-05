<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useEcho, type ApplicationStatusChangedPayload } from '../../composables/useEcho'
import { useAuthStore } from '../../features/auth/stores/useAuthStore'
import { useCandidateApplicationsStore } from '../../features/candidate/stores/useCandidateApplicationsStore'
import Pagination from '../../components/Pagination.vue'
import { useToast } from '../../composables/useToast'
import AppModal from '../../components/AppModal.vue'
import { Filter, Briefcase, Calendar, FileText, XCircle, RotateCcw } from 'lucide-vue-next'
import { format } from 'date-fns'

const authStore = useAuthStore()
const store = useCandidateApplicationsStore()
const echo = useEcho()
const { success: showSuccess, error: showError } = useToast()

const cancelModalOpen = ref(false)
const appToCancel = ref<number | null>(null)

onMounted(async () => {
  await store.loadPage(1)
  if (authStore.user?.id) {
    await echo.prepareRealtimeAuth().catch(() => undefined)

    echo.subscribePrivate<ApplicationStatusChangedPayload>(
      `application-status.candidate.${authStore.user.id}`,
      '.ApplicationStatusChanged',
      (payload) => {
        const index = store.applications.findIndex((a) => a.id === payload.application_id)
        if (index !== -1) {
          store.applications[index].status = payload.status
          store.applications[index].decision = {
            note: null,
            decided_at: payload.changed_at,
          }
        }
        showSuccess(`Application status updated: ${payload.status}`)
      },
    )
  }
})

function confirmCancel(id: number) {
  appToCancel.value = id
  cancelModalOpen.value = true
}

async function doCancel() {
  if (!appToCancel.value) return
  
  try {
    await store.cancelApplication(appToCancel.value)
    showSuccess('Application cancelled successfully')
    cancelModalOpen.value = false
    appToCancel.value = null
  } catch (e) {
    showError('Failed to cancel application')
  }
}

const statusOptions = [
  { value: '', label: 'All Statuses' },
  { value: 'submitted', label: 'Submitted' },
  { value: 'under_review', label: 'Under Review' },
  { value: 'accepted', label: 'Accepted' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'cancelled', label: 'Cancelled' },
]


function getStatusBadge(status: string) {
  switch (status) {
    case 'accepted': return 'bg-emerald-100 text-emerald-800 border-emerald-200'
    case 'rejected': return 'bg-red-100 text-red-800 border-red-200'
    case 'under_review':
    case 'pending': 
    case 'submitted': return 'bg-yellow-100 text-yellow-800 border-yellow-200'
    default: return 'bg-slate-100 text-slate-800 border-slate-200'
  }
}

function getStatusLabel(status: string) {
  switch (status) {
    case 'under_review': return 'Under Review'
    default: return status.charAt(0).toUpperCase() + status.slice(1)
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Applications</h1>
      <p class="mt-1 text-sm text-slate-500">Track and manage your job applications.</p>
    </div>

    <!-- Filters Topbar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
      <div class="flex flex-wrap items-end gap-4">
        <!-- Status Filter -->
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">Status</label>
          <div class="relative">
            <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            <select
              :value="store.currentStatusFilter || ''"
              @change="(e) => store.setFilterAndLoad((e.target as HTMLSelectElement).value || undefined)"
              class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-8 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer"
            >
              <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
          </div>
        </div>

        <!-- Date Range -->
        <div class="flex-[2] min-w-[300px] grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">From Date</label>
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              <input
                type="date"
                v-model="store.fromDate"
                @change="store.loadPage(1)"
                class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all"
              >
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5 ml-1">To Date</label>
            <div class="relative">
              <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              <input
                type="date"
                v-model="store.toDate"
                @change="store.loadPage(1)"
                class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all"
              >
            </div>
          </div>
        </div>

        <!-- Reset Button -->
        <div class="flex items-center pb-0.5">
          <button
            v-if="store.currentStatusFilter || store.fromDate || store.toDate"
            @click="store.resetFilters()"
            class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-500 hover:text-red-600 transition-colors border border-transparent rounded-lg hover:bg-red-50"
          >
            <RotateCcw class="w-4 h-4" />
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="store.isFetching" class="py-12 flex justify-center bg-white rounded-xl border border-slate-200 shadow-sm">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="store.applications.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
      <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <Briefcase class="w-8 h-8 text-slate-400" />
      </div>
      <h3 class="text-lg font-semibold text-slate-900">No applications found</h3>
      <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">You haven't submitted any applications that match the current filters.</p>
      
      <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
        <button
          v-if="store.currentStatusFilter || store.fromDate || store.toDate"
          @click="store.resetFilters()"
          class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors gap-2"
        >
          <RotateCcw class="w-4 h-4" />
          Clear filters
        </button>
        
        <RouterLink to="/candidate/jobs" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm gap-2">
          Browse Jobs <Briefcase class="w-4 h-4" />
        </RouterLink>
      </div>
    </div>

    <!-- List -->
    <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="divide-y divide-slate-100">
        <div
          v-for="app in store.applications"
          :key="app.id"
          class="p-6 hover:bg-slate-50/50 transition-colors"
        >
          <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-lg font-semibold text-slate-900 truncate">
                  {{ app.job_listing?.title }}
                </h2>
                <span :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold shadow-sm', getStatusBadge(app.status)]">
                  {{ getStatusLabel(app.status) }}
                </span>
              </div>
              
              <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                <div class="flex items-center gap-1.5">
                  <Briefcase class="w-4 h-4 text-slate-400" />
                  <span class="truncate">{{ app.job_listing?.employer?.company_name || 'Unknown Company' }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                  <Calendar class="w-4 h-4 text-slate-400" />
                  <span>Applied {{ format(new Date(app.submitted_at), 'MMM d, yyyy') }}</span>
                </div>
                <div v-if="app.resume?.original_name" class="flex items-center gap-1.5">
                  <FileText class="w-4 h-4 text-slate-400" />
                  <span class="truncate max-w-[200px]">{{ app.resume.original_name }}</span>
                </div>
              </div>
              
              <div v-if="app.decision?.note" class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-100">
                <p class="text-sm text-slate-700">
                  <span class="font-medium text-slate-900 block mb-1">Feedback:</span> 
                  {{ app.decision.note }}
                </p>
              </div>
            </div>
            
            <div class="flex items-center md:flex-col justify-end gap-3 shrink-0">
              <button
                v-if="['submitted', 'under_review'].includes(app.status)"
                @click="confirmCancel(app.id)"
                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors gap-2"
              >
                <XCircle class="w-4 h-4" />
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="store.meta && store.meta.last_page > 1" class="border-t border-slate-200 p-4 flex justify-center bg-slate-50">
        <Pagination
          :links="store.links"
          :meta="store.meta"
          @page-change="store.loadPage"
        />
      </div>
    </div>
    
    <!-- Cancel Modal -->
    <AppModal
      :is-open="cancelModalOpen"
      title="Cancel Application"
      @close="cancelModalOpen = false"
    >
      <div class="p-1">
        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
          <XCircle class="w-6 h-6 text-red-600" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900 mb-2">Are you sure?</h3>
        <p class="text-slate-600 mb-6 text-sm">
          Are you sure you want to cancel this application? This action cannot be undone and the employer will be notified.
        </p>
        
        <div class="flex justify-end gap-3">
          <button
            @click="cancelModalOpen = false"
            :disabled="store.isCancelling"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors border border-transparent"
          >
            Keep it
          </button>
          
          <button
            @click="doCancel"
            :disabled="store.isCancelling"
            class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ store.isCancelling ? 'Cancelling...' : 'Yes, cancel application' }}
          </button>
        </div>
      </div>
    </AppModal>
  </div>
</template>
