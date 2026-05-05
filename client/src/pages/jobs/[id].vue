<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePublicJobs } from '../../composables/usePublicJobs'
import JobDetail from '../../features/jobs/components/JobDetail.vue'
import AppModal from '../../components/AppModal.vue'
import AppButton from '../../components/AppButton.vue'
import { useAuthStore } from '../../features/auth/stores/useAuthStore'
import { useApplicationSubmit } from '../../features/candidate/composables/useApplicationSubmit'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'
import { useRouter } from 'vue-router'
import { useToast } from '../../composables/useToast'
import ResumeUpload from '../../features/candidate/components/ResumeUpload.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const profileStore = useCandidateProfileStore()
const { submitApplication, isSubmitting } = useApplicationSubmit()
const { success: showSuccess, error: showError } = useToast()

const { selectedJob, isDetailLoading, formError, loadJob } = usePublicJobs()

const isModalOpen = ref(false)
const submissionMode = ref<'resume' | 'contact'>('resume')
const selectedResumeId = ref<number | null>(null)
const useProfileContact = ref(true)
const coverLetter = ref('')
const resumes = ref<any[]>([])

const jobId = computed(() => {
  const params = route.params as Record<string, string | string[] | undefined>
  const rawId = params.id

  if (Array.isArray(rawId)) {
    return rawId[0] ?? ''
  }

  return rawId ? String(rawId) : ''
})

async function fetchResumes() {
  if (authStore.user?.role !== 'candidate') return
  try {
    const { fetchResumes } = await import('../../features/candidate/composables/useCandidateProfile').then(m => m.useCandidateProfile())
    const response = await fetchResumes()
    resumes.value = response.data
    
    // Set selected if not set and we have a default or first
    if (!selectedResumeId.value) {
      if (profileStore.defaultResumeId) {
        selectedResumeId.value = profileStore.defaultResumeId
      } else if (resumes.value.length > 0) {
        selectedResumeId.value = resumes.value[0].id
      }
    }
  } catch (e) {
    console.error('Failed to load resumes')
  }
}

async function load(): Promise<void> {
  if (!jobId.value) {
    return
  }

  await loadJob(jobId.value)
}

watch(jobId, (newId, oldId) => {
  if (newId && newId !== oldId) {
    load()
  }
})

onMounted(async () => {
  await load()
  if (authStore.user?.role === 'candidate') {
    await Promise.all([
      profileStore.loadProfile(),
      fetchResumes()
    ])
  }
})

function handleApplyClick() {
  if (!authStore.isAuthenticated) {
    router.push({ path: '/auth/login', query: { redirect: route.fullPath } })
    return
  }
  
  if (authStore.user?.role !== 'candidate') {
    showError('Only candidates can apply for jobs.')
    return
  }

  isModalOpen.value = true
}

function onResumeUploaded(resume: any) {
  resumes.value.unshift(resume)
  selectedResumeId.value = resume.id
  showSuccess('Resume uploaded and selected!')
}

async function doSubmitApplication() {
  if (!jobId.value) return

  try {
    await submitApplication(jobId.value, {
      submission_mode: submissionMode.value,
      resume_id: submissionMode.value === 'resume' ? selectedResumeId.value : null,
      use_profile_contact: useProfileContact.value,
      cover_letter: coverLetter.value || null,
    })
    
    showSuccess('Application submitted successfully!')
    isModalOpen.value = false
    router.push('/candidate/applications')
  } catch (e: any) {
    showError(e.response?.data?.message || 'Failed to submit application.')
  }
}
</script>

<template>
  <section class="grid gap-5 py-8 sm:py-10">
    <RouterLink
      to="/jobs"
      class="inline-flex w-fit items-center text-sm font-semibold text-emerald-700 hover:text-emerald-800"
    >
      &larr; Back to jobs
    </RouterLink>

    <div v-if="isDetailLoading" class="grid gap-3" role="status" aria-live="polite" aria-busy="true">
      <div class="h-8 w-2/3 animate-pulse rounded bg-slate-200" />
      <div class="h-5 w-1/2 animate-pulse rounded bg-slate-200" />
      <div class="h-64 animate-pulse rounded bg-slate-200" />
    </div>

    <div
      v-else-if="formError"
      class="rounded-lg border border-dashed border-red-200 bg-red-50 px-5 py-12 text-center"
      role="alert"
    >
      <p class="text-base font-medium text-slate-900">Unable to load this job</p>
      <p class="mt-1 text-sm text-red-700">{{ formError }}</p>
      <button
        type="button"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        @click="load"
      >
        Retry
      </button>
    </div>

    <div
      v-else-if="!selectedJob"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-5 py-12 text-center"
    >
      <p class="text-base font-medium text-slate-900">Job not found</p>
      <p class="mt-1 text-sm text-slate-600">
        This listing is unavailable or no longer public.
      </p>
      <RouterLink
        to="/jobs"
        class="mt-4 inline-flex h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
      >
        Back to jobs
      </RouterLink>
    </div>

    <JobDetail v-else :job="selectedJob" @apply="handleApplyClick" />

    <AppModal
      :is-open="isModalOpen"
      title="Apply for Job"
      @close="isModalOpen = false"
    >
      <form @submit.prevent="doSubmitApplication" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Submission Mode</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2">
              <input type="radio" v-model="submissionMode" value="resume" class="text-indigo-600 focus:ring-indigo-500">
              <span class="text-sm text-gray-700">Use Resume</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" v-model="submissionMode" value="contact" class="text-indigo-600 focus:ring-indigo-500">
              <span class="text-sm text-gray-700">Contact Details Only</span>
            </label>
          </div>
        </div>

        <div v-if="submissionMode === 'resume'" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Resume</label>
            <select
              v-if="resumes.length > 0"
              v-model="selectedResumeId"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              required
            >
              <option v-for="resume in resumes" :key="resume.id" :value="resume.id">
                {{ resume.original_name }} {{ profileStore.defaultResumeId === resume.id ? '(Default)' : '' }}
              </option>
            </select>
            <p v-else class="text-sm text-amber-600 bg-amber-50 p-3 rounded border border-amber-200">
              You haven't uploaded any resumes yet.
            </p>
          </div>

          <div class="pt-2" v-if="resumes.length < 3">
            <p class="text-xs text-gray-500 mb-2">Or upload a new one (Max 3):</p>
            <ResumeUpload @uploaded="onResumeUploaded" @error="showError" />
          </div>
          <div v-else class="pt-2">
            <p class="text-xs text-amber-600 font-medium">
              You have reached the limit of 3 resumes. Delete one from your 
              <RouterLink to="/candidate/profile" class="underline">profile</RouterLink> 
              if you want to upload a new one.
            </p>
          </div>

          <p class="text-xs text-gray-400">
            You can manage your default resume in your 
            <RouterLink to="/candidate/profile" class="text-emerald-600 hover:underline">profile</RouterLink>.
          </p>
        </div>

        <div>
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="useProfileContact" class="text-indigo-600 focus:ring-indigo-500 rounded">
            <span class="text-sm font-medium text-gray-700">Include Profile Contact Info</span>
          </label>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter (Optional)</label>
          <textarea
            v-model="coverLetter"
            rows="4"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Introduce yourself to the employer..."
          ></textarea>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <AppButton type="button" variant="secondary" @click="isModalOpen = false">Cancel</AppButton>
          <AppButton 
            type="submit" 
            variant="primary" 
            :disabled="isSubmitting || (submissionMode === 'resume' && !selectedResumeId)"
          >
            {{ isSubmitting ? 'Submitting...' : 'Submit Application' }}
          </AppButton>
        </div>
      </form>
    </AppModal>
  </section>
</template>

