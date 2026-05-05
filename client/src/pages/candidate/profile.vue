<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'
import ResumeUpload from '../../features/candidate/components/ResumeUpload.vue'
import type { Resume } from '../../features/candidate/types'
import { useToast } from '../../composables/useToast'
import { Save, User as UserIcon, MapPin, Phone, FileText, CheckCircle, Trash2, ShieldCheck, Tag } from 'lucide-vue-next'

const store = useCandidateProfileStore()
const { success: showSuccess, error: showError } = useToast()

const summary = ref('')
const locationText = ref('')
const phone = ref('')
const skillsText = ref('')
const resumes = ref<Resume[]>([])

onMounted(async () => {
  await store.loadProfile()
  if (store.profile) {
    summary.value = store.profile.summary || ''
    locationText.value = store.profile.location_text || ''
    phone.value = store.profile.phone || ''
    skillsText.value = store.profile.skills?.join(', ') || ''
  }
  await fetchResumes()
})

async function fetchResumes() {
  try {
    const { fetchResumes } = await import('../../features/candidate/composables/useCandidateProfile').then(m => m.useCandidateProfile())
    const response = await fetchResumes()
    resumes.value = response.data
  } catch (e) {
    showError('Failed to load resumes')
  }
}

async function saveProfile() {
  const skills = skillsText.value
    .split(',')
    .map((s) => s.trim())
    .filter((s) => s.length > 0)

  try {
    await store.saveProfile({
      summary: summary.value,
      location_text: locationText.value,
      phone: phone.value,
      skills,
    })
    showSuccess('Profile updated successfully')
  } catch (e) {
    showError('Failed to update profile')
  }
}

async function setDefaultResume(id: number) {
  try {
    await store.saveProfile({
      default_resume_id: id,
    })
    showSuccess('Default resume updated')
  } catch (e) {
    showError('Failed to update default resume')
  }
}

async function deleteResume(id: number) {
  if (!confirm('Are you sure you want to delete this resume?')) return

  try {
    const { deleteResume } = await import('../../features/candidate/composables/useCandidateProfile').then(m => m.useCandidateProfile())
    await deleteResume(id)
    
    if (store.defaultResumeId === id) {
      await store.loadProfile()
    }
    
    await fetchResumes()
    showSuccess('Resume deleted')
  } catch (e) {
    showError('Failed to delete resume')
  }
}

function onResumeUploaded(resume: Resume) {
  showSuccess('Resume uploaded successfully')
  resumes.value.unshift(resume)
  
  if (!store.defaultResumeId) {
    setDefaultResume(resume.id)
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Profile</h1>
      <p class="mt-1 text-sm text-slate-500">Manage your personal information and resumes.</p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Form -->
      <div class="lg:col-span-2">
        <form @submit.prevent="saveProfile" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
          <div class="p-6 border-b border-slate-100 flex items-center gap-2">
            <UserIcon class="w-5 h-5 text-emerald-600" />
            <h2 class="text-lg font-semibold text-slate-900">Personal Information</h2>
          </div>
          
          <div class="p-6 space-y-6 flex-1">
            <!-- Summary -->
            <div>
              <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-1.5">
                <FileText class="w-4 h-4 text-slate-400" /> Professional Summary
              </label>
              <textarea
                v-model="summary"
                rows="4"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500/20 focus:outline-none transition-colors shadow-sm"
                placeholder="A brief summary of your experience and goals"
              ></textarea>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Location -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-1.5">
                  <MapPin class="w-4 h-4 text-slate-400" /> Location
                </label>
                <input
                  v-model="locationText"
                  type="text"
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500/20 focus:outline-none transition-colors shadow-sm"
                  placeholder="e.g. San Francisco, CA"
                >
              </div>
              
              <!-- Phone -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-1.5">
                  <Phone class="w-4 h-4 text-slate-400" /> Phone
                </label>
                <input
                  v-model="phone"
                  type="tel"
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500/20 focus:outline-none transition-colors shadow-sm"
                  placeholder="+1 (555) 000-0000"
                >
              </div>
            </div>
            
            <!-- Skills -->
            <div>
              <label class="flex items-center gap-2 text-sm font-medium text-slate-700 mb-1.5">
                <Tag class="w-4 h-4 text-slate-400" /> Skills
              </label>
              <input
                v-model="skillsText"
                type="text"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500/20 focus:outline-none transition-colors shadow-sm"
                placeholder="Vue.js, Laravel, Tailwind CSS"
              >
              <p class="mt-1.5 text-xs text-slate-500">Separate skills with commas.</p>
            </div>
          </div>
          
          <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button
              type="submit"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-50"
              :disabled="store.isUpdating"
            >
              <Save class="w-4 h-4" />
              {{ store.isUpdating ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Resumes Sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-full flex flex-col">
          <div class="p-6 border-b border-slate-100 flex items-center gap-2">
            <FileText class="w-5 h-5 text-emerald-600" />
            <h2 class="text-lg font-semibold text-slate-900">Resumes</h2>
          </div>
          
          <div class="p-6 flex-1 flex flex-col">
            <ResumeUpload
              v-if="resumes.length < 3"
              @uploaded="onResumeUploaded"
              @error="showError"
            />
            <div v-else class="p-4 bg-amber-50 rounded-lg border border-amber-200 flex items-start gap-3">
              <ShieldCheck class="w-5 h-5 text-amber-600 shrink-0" />
              <p class="text-sm text-amber-800">
                You have reached the maximum of 3 resumes. Please delete an older one to upload a new version.
              </p>
            </div>
            
            <div class="mt-8 space-y-3 flex-1">
              <div v-if="resumes.length === 0" class="h-32 flex flex-col items-center justify-center text-center">
                <FileText class="w-8 h-8 text-slate-300 mb-2" />
                <p class="text-sm text-slate-500">No resumes uploaded yet.</p>
              </div>
              
              <div
                v-for="resume in resumes"
                :key="resume.id"
                class="group flex flex-col rounded-xl border p-4 transition-all"
                :class="store.defaultResumeId === resume.id ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-200 bg-white hover:border-slate-300'"
              >
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900 truncate" :title="resume.original_name">
                      {{ resume.original_name }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">
                      Uploaded {{ new Date(resume.created_at).toLocaleDateString() }}
                    </p>
                  </div>
                  
                  <span v-if="store.defaultResumeId === resume.id" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 shrink-0">
                    <CheckCircle class="w-3 h-3" /> Default
                  </span>
                </div>
                
                <div class="mt-4 flex items-center justify-between pt-3 border-t" :class="store.defaultResumeId === resume.id ? 'border-emerald-200' : 'border-slate-100'">
                  <button
                    v-if="store.defaultResumeId !== resume.id"
                    @click="setDefaultResume(resume.id)"
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 transition-colors"
                  >
                    Set as Default
                  </button>
                  <span v-else class="text-xs text-emerald-600 font-medium">Active Default</span>
                  
                  <button
                    @click="deleteResume(resume.id)"
                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete Resume"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
