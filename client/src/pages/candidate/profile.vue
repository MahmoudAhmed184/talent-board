<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useCandidateProfileStore } from '../../features/candidate/stores/useCandidateProfileStore'
import ResumeUpload from '../../features/candidate/components/ResumeUpload.vue'
import type { Resume } from '../../features/candidate/types'
import { useToast } from '../../composables/useToast'

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
    
    // If deleted the default resume, update store
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
  
  // Set as default if it's the first one
  if (!store.defaultResumeId) {
    setDefaultResume(resume.id)
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-8 text-white">My Profile</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Profile Form -->
      <div class="md:col-span-2 bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
        <h2 class="text-xl font-semibold mb-6 text-white">Personal Information</h2>
        
        <form @submit.prevent="saveProfile" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Professional Summary</label>
            <textarea
              v-model="summary"
              rows="4"
              class="w-full rounded-md bg-gray-900 border-gray-600 text-white focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="A brief summary of your experience and goals"
            ></textarea>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Location</label>
              <input
                v-model="locationText"
                type="text"
                class="w-full rounded-md bg-gray-900 border-gray-600 text-white focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="e.g. San Francisco, CA"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-1">Phone</label>
              <input
                v-model="phone"
                type="tel"
                class="w-full rounded-md bg-gray-900 border-gray-600 text-white focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="+1 (555) 000-0000"
              >
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Skills (comma separated)</label>
            <input
              v-model="skillsText"
              type="text"
              class="w-full rounded-md bg-gray-900 border-gray-600 text-white focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="Vue.js, Laravel, TypeScript"
            >
          </div>
          
          <div class="flex justify-end">
            <button
              type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium transition-colors"
              :disabled="store.isUpdating"
            >
              {{ store.isUpdating ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Resumes Sidebar -->
      <div class="space-y-6">
        <div class="bg-gray-800 rounded-lg shadow p-6 border border-gray-700">
          <h2 class="text-xl font-semibold mb-6 text-white">Resumes</h2>
          
          <ResumeUpload
            v-if="resumes.length < 3"
            @uploaded="onResumeUploaded"
            @error="showError"
          />
          <div v-else class="p-4 bg-amber-900/20 border border-amber-700/50 rounded-md">
            <p class="text-sm text-amber-400">
              You have reached the limit of 3 resumes. Delete an existing one to upload a new version.
            </p>
          </div>
          
          <div class="mt-6 space-y-4">
            <p v-if="resumes.length === 0" class="text-gray-400 text-sm text-center py-4">
              No resumes uploaded yet.
            </p>
            
            <div
              v-for="resume in resumes"
              :key="resume.id"
              class="bg-gray-900 rounded-md p-4 border flex flex-col gap-3 transition-colors"
              :class="store.defaultResumeId === resume.id ? 'border-indigo-500' : 'border-gray-700 hover:border-gray-600'"
            >
              <div class="flex items-start justify-between">
                <div class="flex flex-col overflow-hidden">
                  <span class="text-sm font-medium text-gray-200 truncate" :title="resume.original_name">
                    {{ resume.original_name }}
                  </span>
                  <span class="text-xs text-gray-500 mt-1">
                    {{ new Date(resume.created_at).toLocaleDateString() }}
                  </span>
                </div>
                
                <span v-if="store.defaultResumeId === resume.id" class="px-2 py-1 bg-indigo-500/20 text-indigo-300 text-xs rounded-full border border-indigo-500/30">
                  Default
                </span>
              </div>
              
              <div class="flex items-center gap-2 mt-2 pt-3 border-t border-gray-800">
                <button
                  v-if="store.defaultResumeId !== resume.id"
                  @click="setDefaultResume(resume.id)"
                  class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                >
                  Set as Default
                </button>
                <div class="flex-grow"></div>
                <button
                  @click="deleteResume(resume.id)"
                  class="text-xs text-red-400 hover:text-red-300 font-medium"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
