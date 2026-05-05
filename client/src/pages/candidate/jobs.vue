<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Briefcase, FileText, CheckCircle } from 'lucide-vue-next'
import { useJobSearch } from '../../composables/useJobSearch'
import Pagination from '../../components/Pagination.vue'
import AppModal from '../../components/AppModal.vue'
import SearchFilters from '../../components/SearchFilters.vue'
import JobCard from '../../features/jobs/components/JobCard.vue'
import { useToast } from '../../composables/useToast'
import { http } from '../../http'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'

const {
  jobs,
  paginationLinks,
  paginationMeta,
  isListLoading,
  executeSearch,
} = useJobSearch()

const profileStore = useCandidateProfileStore()
const { success: showSuccess, error: showError } = useToast()

const selectedJob = ref<any>(null)
const isApplyModalOpen = ref(false)
const isSubmitting = ref(false)

// Application Form State
const submissionMode = ref<'resume' | 'profile'>('resume')
const coverLetter = ref('')

onMounted(async () => {
  await executeSearch(1)
  await profileStore.loadProfile()
})

const hasDefaultResume = computed(() => !!profileStore.defaultResumeId)

function openApplyModal(job: any) {
  selectedJob.value = job
  submissionMode.value = hasDefaultResume.value ? 'resume' : 'profile'
  coverLetter.value = ''
  isApplyModalOpen.value = true
}

function closeApplyModal() {
  isApplyModalOpen.value = false
  setTimeout(() => {
    selectedJob.value = null
  }, 300)
}

async function submitApplication() {
  if (!selectedJob.value) return
  
  if (submissionMode.value === 'resume' && !hasDefaultResume.value) {
    showError('Please set a default resume in your profile first')
    return
  }

  isSubmitting.value = true
  
  try {
    await http.post(`/api/v1/jobs/${selectedJob.value.id}/applications`, {
      submission_mode: submissionMode.value,
      resume_id: submissionMode.value === 'resume' ? profileStore.defaultResumeId : null,
      use_profile_contact: submissionMode.value === 'profile',
      cover_letter: coverLetter.value || null,
    })
    
    showSuccess(`Successfully applied to ${selectedJob.value.title}`)
    closeApplyModal()
  } catch (error: any) {
    showError(error.response?.data?.message || 'Failed to submit application')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Browse Jobs</h1>
      <p class="mt-1 text-sm text-slate-500">Discover and apply to your next great opportunity.</p>
    </div>

    <!-- Layout: Sidebar Filters + Job List -->
    <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8 items-start">
      
      <!-- Filters Sidebar -->
      <SearchFilters @search="executeSearch(1)" class="sticky top-24" />
      
      <!-- Job List Column -->
      <div class="space-y-4">
        
        <div v-if="isListLoading" class="grid gap-4">
          <div v-for="i in 3" :key="i" class="h-40 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
        </div>
        
        <div v-else-if="jobs.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <Briefcase class="w-8 h-8 text-slate-400" />
          </div>
          <h3 class="text-lg font-semibold text-slate-900">No jobs found</h3>
          <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">Try adjusting your search filters to find what you're looking for.</p>
        </div>

        <div v-else class="grid gap-4">
          <div
            v-for="job in jobs"
            :key="job.id"
            class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all"
          >
            <JobCard :job="job" @apply="openApplyModal" />
          </div>
          
          <div v-if="paginationMeta && paginationMeta.last_page > 1" class="mt-4 flex justify-center">
            <Pagination
              :links="paginationLinks"
              :meta="paginationMeta"
              :disabled="isListLoading"
              @page-change="executeSearch"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Apply Modal -->
    <AppModal
      :is-open="isApplyModalOpen"
      title="Submit Application"
      @close="closeApplyModal"
    >
      <div v-if="selectedJob" class="p-1 space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">{{ selectedJob.title }}</h3>
          <p class="text-sm text-slate-500">{{ selectedJob.employer?.company_name || 'Company' }}</p>
        </div>
        
        <div class="space-y-4">
          <label class="block text-sm font-medium text-slate-900">Submission Method</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Use Resume Option -->
            <label
              class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all"
              :class="submissionMode === 'resume' ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/10' : 'border-slate-200 hover:border-slate-300'"
            >
              <input type="radio" name="submission_mode" value="resume" v-model="submissionMode" class="sr-only">
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <span class="block text-sm font-medium text-slate-900">Use Default Resume</span>
                  <span class="mt-1 flex items-center text-xs text-slate-500">
                    {{ hasDefaultResume ? profileStore.defaultResume?.original_name : 'No default resume set' }}
                  </span>
                </span>
              </span>
              <CheckCircle v-if="submissionMode === 'resume'" class="h-5 w-5 text-emerald-600 shrink-0" />
            </label>
            
            <!-- Use Profile Contact Option -->
            <label
              class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all"
              :class="submissionMode === 'profile' ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/10' : 'border-slate-200 hover:border-slate-300'"
            >
              <input type="radio" name="submission_mode" value="profile" v-model="submissionMode" class="sr-only">
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <span class="block text-sm font-medium text-slate-900">Use Profile Details</span>
                  <span class="mt-1 flex items-center text-xs text-slate-500">Submit summary & skills</span>
                </span>
              </span>
              <CheckCircle v-if="submissionMode === 'profile'" class="h-5 w-5 text-emerald-600 shrink-0" />
            </label>
          </div>
          
          <div v-if="submissionMode === 'resume' && !hasDefaultResume" class="text-sm text-red-600 mt-2">
            You must set a default resume in your Profile to use this option.
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-900 mb-1.5">Cover Letter (Optional)</label>
          <textarea
            v-model="coverLetter"
            rows="4"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500/20 focus:outline-none transition-colors shadow-sm"
            placeholder="Tell the employer why you're a great fit for this role..."
          ></textarea>
        </div>
        
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button
            @click="closeApplyModal"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors"
            :disabled="isSubmitting"
          >
            Cancel
          </button>
          
          <button
            @click="submitApplication"
            class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-50"
            :disabled="isSubmitting || (submissionMode === 'resume' && !hasDefaultResume)"
          >
            {{ isSubmitting ? 'Submitting...' : 'Submit Application' }}
          </button>
        </div>
      </div>
    </AppModal>
  </div>
</template>
