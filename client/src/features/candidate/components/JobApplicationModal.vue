<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { 
  FileText, 
  Upload, 
  CheckCircle, 
  AlertCircle, 
  Loader2, 
  X,
  FileUp,
  ChevronRight
} from 'lucide-vue-next'
import AppModal from '../../../components/AppModal.vue'
import { useCandidateProfileStore } from '../stores/useCandidateProfileStore'
import { useCandidateApplicationsStore } from '../stores/useCandidateApplicationsStore'
import { useToast } from '../../../composables/useToast'
import { http } from '../../../http'

const props = defineProps<{
  isOpen: boolean
  job: any
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success'): void
}>()

const profileStore = useCandidateProfileStore()
const applicationStore = useCandidateApplicationsStore()
const { success: showSuccess, error: showError } = useToast()

const isSubmitting = ref(false)
const isLoadingResumes = ref(false)
const selectedResumeId = ref<number | null>(null)
const selectedFile = ref<File | null>(null)
const coverLetter = ref('')
const fileInput = ref<HTMLInputElement | null>(null)

// Initialize modal state
onMounted(async () => {
  if (props.isOpen) {
    await loadInitialData()
  }
})

// Re-load if modal opens
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    await loadInitialData()
  } else {
    resetForm()
  }
})

async function loadInitialData() {
  isLoadingResumes.value = true
  try {
    if (!profileStore.profile) {
      await profileStore.loadProfile()
    }
    await profileStore.loadResumes()
    
    // Smart auto-selection
    if (profileStore.defaultResumeId) {
      selectedResumeId.value = profileStore.defaultResumeId
    } else if (profileStore.resumes.length === 1) {
      selectedResumeId.value = profileStore.resumes[0].id
    }
  } catch (error) {
    showError('Failed to load resumes. Please try again.')
  } finally {
    isLoadingResumes.value = false
  }
}

function resetForm() {
  selectedResumeId.value = profileStore.defaultResumeId || (profileStore.resumes.length === 1 ? profileStore.resumes[0].id : null)
  selectedFile.value = null
  coverLetter.value = ''
  if (fileInput.value) fileInput.value.value = ''
}

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  // Validate file
  const allowedTypes = ['.pdf', '.doc', '.docx']
  const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
  
  if (!allowedTypes.includes(extension)) {
    showError('Invalid file type. Please upload a PDF, DOC, or DOCX.')
    target.value = ''
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    showError('File is too large. Maximum size is 10MB.')
    target.value = ''
    return
  }

  selectedFile.value = file
  selectedResumeId.value = null // Deselect existing resume if uploading new
}

function selectExistingResume(id: number) {
  selectedResumeId.value = id
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

async function submitApplication() {
  if (!selectedResumeId.value && !selectedFile.value) {
    showError('Please select or upload a resume.')
    return
  }

  isSubmitting.value = true
  
  const formData = new FormData()
  if (selectedResumeId.value) {
    formData.append('resume_id', selectedResumeId.value.toString())
  } else if (selectedFile.value) {
    formData.append('resume_file', selectedFile.value)
  }
  
  if (coverLetter.value) {
    formData.append('cover_letter', coverLetter.value)
  }

  try {
    await http.post(`/api/v1/jobs/${props.job.id}/applications`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    showSuccess(`Successfully applied to ${props.job.title}`)
    applicationStore.markJobAsApplied(props.job.id)
    emit('success')
    emit('close')
  } catch (error: any) {
    handleSubmissionError(error)
  } finally {
    isSubmitting.value = false
  }
}

function handleSubmissionError(error: any) {
  const status = error.response?.status
  const data = error.response?.data
  
  if (status === 422) {
    const message = data.errors ? Object.values(data.errors).flat()[0] : data.message
    showError(message || 'Validation failed.')
  } else if (status === 413) {
    showError('File is too large for the server to process.')
  } else {
    showError(data?.message || 'Failed to submit application. Please try again.')
  }
}

function triggerFileSelect() {
  fileInput.value?.click()
}
</script>

<template>
  <AppModal
    :is-open="isOpen"
    :title="'Apply for ' + job?.title"
    size="lg"
    @close="!isSubmitting && emit('close')"
  >
    <div class="relative overflow-hidden">
      <!-- Loading Overlay -->
      <div 
        v-if="isSubmitting" 
        class="absolute inset-0 z-50 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center transition-all duration-300"
      >
        <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-100 flex flex-col items-center space-y-4">
          <div class="relative">
            <div class="w-16 h-16 border-4 border-emerald-100 border-t-emerald-600 rounded-full animate-spin"></div>
            <FileText class="absolute inset-0 m-auto w-6 h-6 text-emerald-600" />
          </div>
          <div class="text-center">
            <h3 class="text-lg font-semibold text-slate-900">Submitting Application</h3>
            <p class="text-sm text-slate-500">Please wait while we process your request...</p>
          </div>
        </div>
      </div>

      <div class="p-6 space-y-8">
        <!-- Job Info Summary -->
        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
          <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
            <FileText class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <h4 class="font-bold text-slate-900">{{ job?.title }}</h4>
            <p class="text-sm text-slate-500">{{ job?.employer?.company_name || 'Company' }} • {{ job?.location }}</p>
          </div>
        </div>

        <!-- Resume Selection -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
              Select Resume
              <span class="text-xs font-normal text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Required</span>
            </h3>
          </div>

          <div class="grid gap-3">
            <!-- Existing Resumes -->
            <div v-if="isLoadingResumes" class="space-y-3">
              <div v-for="i in 2" :key="i" class="h-16 animate-pulse bg-slate-100 rounded-xl border border-slate-200"></div>
            </div>
            
            <template v-else-if="profileStore.resumes.length > 0">
              <button 
                v-for="resume in profileStore.resumes" 
                :key="resume.id"
                @click="selectExistingResume(resume.id)"
                class="group relative flex items-center gap-4 p-4 rounded-xl border transition-all text-left w-full"
                :class="selectedResumeId === resume.id 
                  ? 'border-emerald-500 bg-emerald-50/30 ring-1 ring-emerald-500' 
                  : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm'"
              >
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                  :class="selectedResumeId === resume.id ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200'"
                >
                  <FileText class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-900 truncate">{{ resume.original_name }}</p>
                  <p class="text-xs text-slate-500">Uploaded on {{ new Date(resume.created_at).toLocaleDateString() }}</p>
                </div>
                <div 
                  class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                  :class="selectedResumeId === resume.id ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 bg-white'"
                >
                  <CheckCircle v-if="selectedResumeId === resume.id" class="w-3.5 h-3.5 text-white" />
                </div>
              </button>
            </template>

            <!-- Upload New Section -->
            <div 
              class="relative group rounded-xl border-2 border-dashed transition-all cursor-pointer overflow-hidden"
              :class="[
                selectedFile ? 'border-emerald-500 bg-emerald-50/10' : 'border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/5',
                isSubmitting ? 'opacity-50 pointer-events-none' : ''
              ]"
              @click="triggerFileSelect"
            >
              <input 
                ref="fileInput" 
                type="file" 
                class="hidden" 
                accept=".pdf,.doc,.docx"
                @change="handleFileSelect"
              >
              
              <div v-if="!selectedFile" class="p-6 flex flex-col items-center justify-center text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                  <FileUp class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-900">Upload New Resume</p>
                  <p class="text-xs text-slate-500">PDF, DOC, DOCX up to 10MB</p>
                </div>
              </div>

              <div v-else class="p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                  <FileText class="w-5 h-5" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-900 truncate">{{ selectedFile.name }}</p>
                  <p class="text-xs text-emerald-600 font-medium">New file ready to upload</p>
                </div>
                <button 
                  @click.stop="selectedFile = null; if (fileInput) fileInput.value = ''" 
                  class="p-2 hover:bg-red-50 rounded-lg text-slate-400 hover:text-red-500 transition-colors"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cover Letter -->
        <div class="space-y-2">
          <label class="text-base font-bold text-slate-900 flex items-center justify-between">
            <span>Cover Letter</span>
            <span class="text-xs font-normal text-slate-400">Optional</span>
          </label>
          <div class="relative">
            <textarea 
              v-model="coverLetter"
              rows="4"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all placeholder:text-slate-400 resize-none"
              placeholder="Explain why you're a good fit for this position..."
              :disabled="isSubmitting"
            ></textarea>
            <div class="absolute bottom-3 right-3 text-[10px] text-slate-400 font-medium">
              {{ coverLetter.length }} / 2000
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 pt-2">
          <button 
            @click="emit('close')"
            class="flex-1 px-4 py-3.5 rounded-xl text-sm font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors disabled:opacity-50"
            :disabled="isSubmitting"
          >
            Cancel
          </button>
          <button 
            @click="submitApplication"
            class="flex-[2] relative px-6 py-3.5 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 active:scale-[0.98] disabled:opacity-50 disabled:shadow-none overflow-hidden group"
            :disabled="isSubmitting || (!selectedResumeId && !selectedFile)"
          >
            <div class="flex items-center justify-center gap-2">
              <span v-if="!isSubmitting">Submit Application</span>
              <span v-else>Processing...</span>
              <ChevronRight v-if="!isSubmitting" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              <Loader2 v-else class="w-4 h-4 animate-spin" />
            </div>
          </button>
        </div>
      </div>

      <!-- Trust Badge -->
      <div class="p-4 bg-slate-50 border-t border-slate-100 text-center">
        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold flex items-center justify-center gap-1.5">
          <CheckCircle class="w-3 h-3" /> Secure Resume Processing
        </p>
      </div>
    </div>
  </AppModal>
</template>

<style scoped>
.ring-1 {
  box-shadow: 0 0 0 1px rgb(16 185 129);
}
</style>
