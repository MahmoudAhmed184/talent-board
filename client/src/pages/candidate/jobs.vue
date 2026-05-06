<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Briefcase, FileText, CheckCircle } from 'lucide-vue-next'
import { useJobSearch } from '../../composables/useJobSearch'
import Pagination from '../../components/Pagination.vue'
import AppModal from '../../components/AppModal.vue'
import SearchFilters from '../../components/SearchFilters.vue'
import JobCard from '../../features/jobs/components/JobCard.vue'
import { useToast } from '../../composables/useToast'
import { http } from '../../http'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'
import { useResumes } from '../../features/candidate/composables/useResumes'
import { useCandidateApplicationsStore } from '../../features/candidate/stores/useCandidateApplicationsStore'

const {
  jobs,
  paginationLinks,
  paginationMeta,
  isListLoading,
  executeSearch,
  store,
} = useJobSearch()

const profileStore = useCandidateProfileStore()
const applicationStore = useCandidateApplicationsStore()
const { success: showSuccess, error: showError } = useToast()

const selectedJob = ref<any>(null)
const isApplyModalOpen = ref(false)
const isSubmitting = ref(false)

const { resumes, fetchResumes, uploadResume, isLoading: isResumesLoading } = useResumes()

// Application Form State
const selectedResumeId = ref<number | null>(null)
const coverLetter = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const isUploading = ref(false)

onMounted(async () => {
  await executeSearch(1)
  await profileStore.loadProfile()
  await applicationStore.fetchAppliedJobIds()
})

async function openApplyModal(job: any) {
  selectedJob.value = job
  coverLetter.value = ''
  isApplyModalOpen.value = true
  
  await fetchResumes()
  
  // Auto-select default resume if available
  if (profileStore.defaultResumeId && resumes.value.some(r => r.id === profileStore.defaultResumeId)) {
    selectedResumeId.value = profileStore.defaultResumeId
  } else if (resumes.value.length > 0) {
    selectedResumeId.value = resumes.value[0].id
  } else {
    selectedResumeId.value = null
  }
}

function closeApplyModal() {
  isApplyModalOpen.value = false
  setTimeout(() => {
    selectedJob.value = null
  }, 300)
}

async function handleFileUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  isUploading.value = true
  try {
    const newResume = await uploadResume(file)
    selectedResumeId.value = newResume.id
    showSuccess('Resume uploaded successfully')
  } catch (error: any) {
    showError(error.response?.data?.message || 'Failed to upload resume')
  } finally {
    isUploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

async function submitApplication() {
  if (!selectedJob.value || !selectedResumeId.value) return
  
  isSubmitting.value = true
  
  try {
    await http.post(`/api/v1/jobs/${selectedJob.value.id}/applications`, {
      resume_id: selectedResumeId.value,
      cover_letter: coverLetter.value || null,
    })
    
    applicationStore.markJobAsApplied(selectedJob.value.id)
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

    <!-- Filters Topbar -->
    <SearchFilters layout="topbar" @search="executeSearch(1)" />
    
    <!-- Job List -->
    <div class="flex-1 min-w-0 space-y-4 w-full">
      
      <div v-if="isListLoading" class="grid gap-4">
        <div v-for="i in 3" :key="i" class="h-40 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
      </div>
      
      <div v-else-if="jobs.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <Briefcase class="w-8 h-8 text-slate-400" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900">No jobs found</h3>
        <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">Try adjusting your search filters to find what you're looking for.</p>
        <button 
          v-if="store.activeFiltersCount > 0"
          @click="store.resetFilters(); executeSearch(1);"
          class="mt-4 text-sm font-medium text-emerald-600 hover:text-emerald-700"
        >
          Clear all filters
        </button>
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
          <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-slate-900">Select Resume (CV)</label>
            <span v-if="resumes.length >= 3" class="text-xs text-slate-500">Max 3 resumes reached</span>
          </div>
          
          <div v-if="isResumesLoading && resumes.length === 0" class="space-y-3">
            <div v-for="i in 2" :key="i" class="h-16 animate-pulse rounded-lg bg-slate-100"></div>
          </div>

          <div v-else-if="resumes.length === 0" class="rounded-lg border border-dashed border-slate-300 p-6 text-center">
            <FileText class="mx-auto h-8 w-8 text-slate-400 mb-2" />
            <p class="text-sm text-slate-600">You don’t have any CVs yet</p>
          </div>

          <div v-else class="grid gap-3">
            <label
              v-for="resume in resumes"
              :key="resume.id"
              class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all"
              :class="selectedResumeId === resume.id ? 'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/10' : 'border-slate-200 hover:border-slate-300'"
            >
              <input type="radio" :value="resume.id" v-model="selectedResumeId" class="sr-only">
              <span class="flex flex-1">
                <span class="flex flex-col">
                  <span class="block text-sm font-medium text-slate-900">{{ resume.original_name }}</span>
                  <span class="mt-1 text-xs text-slate-500">Uploaded on {{ new Date(resume.created_at).toLocaleDateString() }}</span>
                </span>
              </span>
              <CheckCircle v-if="selectedResumeId === resume.id" class="h-5 w-5 text-emerald-600 shrink-0" />
            </label>
          </div>

          <!-- Upload New Resume -->
          <div v-if="resumes.length < 3" class="mt-4">
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept=".pdf,.doc,.docx"
              @change="handleFileUpload"
            >
            <button
              type="button"
              @click="fileInput?.click()"
              class="w-full flex items-center justify-center gap-2 rounded-lg border border-dashed border-slate-300 p-4 text-sm font-medium text-slate-600 hover:border-emerald-500 hover:text-emerald-600 transition-all"
              :disabled="isUploading"
            >
              <template v-if="isUploading">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-emerald-600 border-t-transparent"></div>
                Uploading...
              </template>
              <template v-else>
                <FileText class="h-4 w-4" />
                Upload New CV
              </template>
            </button>
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
            :disabled="isSubmitting || !selectedResumeId"
          >
            {{ isSubmitting ? 'Submitting...' : 'Submit Application' }}
          </button>
        </div>
      </div>
    </AppModal>
  </div>
</template>
