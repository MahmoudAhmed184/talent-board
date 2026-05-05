<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import ApplicationStatusBadge from '../../components/ApplicationStatusBadge.vue'
import { useEcho, type ApplicationStatusChangedPayload } from '../../composables/useEcho'
import { useAuthStore } from '../../features/auth/stores/useAuthStore'
import { useCandidateApplicationsStore } from '../../features/candidate/stores/useCandidateApplicationsStore'
import Pagination from '../../components/Pagination.vue'
import { useToast } from '../../composables/useToast'
import AppModal from '../../components/AppModal.vue'
import AppButton from '../../components/AppButton.vue'

const authStore = useAuthStore()
const store = useCandidateApplicationsStore()
const echo = useEcho()
const { success: showSuccess, error: showError } = useToast()

const cancelModalOpen = ref(false)
const appToCancel = ref<number | null>(null)

onMounted(async () => {
  await store.loadPage(1)
  
  if (!authStore.user?.id) {
    return
  }

  echo.subscribePrivate<ApplicationStatusChangedPayload>(
    `application-status.candidate.${authStore.user.id}`,
    '.ApplicationStatusChanged',
    (payload) => {
      // Update local state if we have the application in view
      const index = store.applications.findIndex(a => a.id === payload.application_id)
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

function onStatusFilterChange(event: Event) {
  const target = event.target as HTMLSelectElement
  store.setFilterAndLoad(target.value || undefined)
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white">My Applications</h1>
        <p class="mt-2 text-sm text-gray-400">
          Track the status of your job applications
        </p>
      </div>
      
      <div class="flex items-center gap-3">
        <label class="text-sm text-gray-300">Filter by Status:</label>
        <select
          :value="store.currentStatusFilter || ''"
          @change="onStatusFilterChange"
          class="rounded-md bg-gray-800 border-gray-700 text-white focus:border-indigo-500 focus:ring-indigo-500 text-sm"
        >
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>
    </div>

    <div v-if="store.isFetching" class="py-12 flex justify-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
    </div>

    <div v-else-if="store.applications.length === 0" class="bg-gray-800 rounded-lg p-12 text-center border border-gray-700">
      <p class="text-gray-300 text-lg">No applications found.</p>
      <p class="text-gray-500 mt-2 text-sm">You haven't submitted any applications that match the current filters.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="app in store.applications"
        :key="app.id"
        class="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-gray-600 transition-colors"
      >
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div>
            <h2 class="text-xl font-semibold text-white">
              {{ app.job_listing?.title }}
            </h2>
            <p class="text-gray-400 mt-1">
              at {{ app.job_listing?.employer?.company_name || 'Unknown Company' }}
            </p>
            
            <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-sm text-gray-400">
              <div>
                <span class="font-medium text-gray-300">Applied:</span>
                {{ new Date(app.submitted_at).toLocaleDateString() }}
              </div>
              <div v-if="app.submission_mode === 'resume'">
                <span class="font-medium text-gray-300">Resume:</span>
                {{ app.resume?.original_name || 'Attached' }}
              </div>
              <div v-else>
                <span class="font-medium text-gray-300">Method:</span>
                Profile Contact
              </div>
            </div>
            
            <div v-if="app.decision?.note" class="mt-4 p-3 bg-gray-900 rounded-md border border-gray-700">
              <p class="text-sm text-gray-300"><span class="font-medium">Employer Note:</span> {{ app.decision.note }}</p>
            </div>
          </div>
          
          <div class="flex flex-col items-end gap-3 min-w-[120px]">
            <ApplicationStatusBadge :status="app.status as any" />
            
            <button
              v-if="['submitted', 'under_review'].includes(app.status)"
              @click="confirmCancel(app.id)"
              class="text-sm text-red-400 hover:text-red-300 font-medium transition-colors"
            >
              Cancel Application
            </button>
          </div>
        </div>
      </div>
      
      <div class="mt-8 flex justify-center">
        <Pagination
          :links="store.links"
          :meta="store.meta"
          @page-change="store.loadPage"
        />
      </div>
    </div>
    
    <AppModal
      :is-open="cancelModalOpen"
      title="Cancel Application"
      @close="cancelModalOpen = false"
    >
      <p class="text-gray-300 mb-6">
        Are you sure you want to cancel this application? This action cannot be undone, and the employer will see that you have withdrawn your application.
      </p>
      
      <div class="flex justify-end gap-3">
        <AppButton
          variant="secondary"
          @click="cancelModalOpen = false"
          :disabled="store.isCancelling"
        >
          Keep Application
        </AppButton>
        
        <AppButton
          variant="danger"
          @click="doCancel"
          :disabled="store.isCancelling"
        >
          {{ store.isCancelling ? 'Cancelling...' : 'Yes, Cancel' }}
        </AppButton>
      </div>
    </AppModal>
  </div>
</template>
