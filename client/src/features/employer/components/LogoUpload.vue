<script setup lang="ts">
import { ref } from 'vue'
import { http } from '../../../http'

const props = defineProps<{
  currentLogoName?: string
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'uploaded', data: any): void
  (e: 'error', message: string): void
  (e: 'deleted'): void
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const isUploading = ref(false)
const isDeleting = ref(false)
const previewUrl = ref<string | null>(null)

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  // Show preview immediately
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target?.result as string
  }
  reader.readAsDataURL(file)

  uploadFile(file)
}

async function uploadFile(file: File) {
  isUploading.value = true
  
  const formData = new FormData()
  formData.append('logo_file', file)
  formData.append('_method', 'PATCH') // Laravel requires _method for PATCH with multipart
  
  try {
    const response = await http.post('/api/v1/employer/profile', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    emit('uploaded', response.data.data)
  } catch (error: any) {
    const message = error.response?.data?.message || 'Failed to upload logo.'
    emit('error', message)
    previewUrl.value = null // Revert preview on error
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  } finally {
    isUploading.value = false
  }
}

async function deleteLogo() {
  if (!confirm('Are you sure you want to remove your company logo?')) return
  
  isDeleting.value = true
  try {
    await http.delete('/api/v1/employer/company-logo')
    previewUrl.value = null
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    emit('deleted')
  } catch (error: any) {
    emit('error', error.response?.data?.message || 'Failed to delete logo.')
  } finally {
    isDeleting.value = false
  }
}

function triggerSelect() {
  fileInput.value?.click()
}
</script>

<template>
  <div class="logo-upload">
    <input
      ref="fileInput"
      type="file"
      accept="image/png, image/jpeg, image/jpg, image/svg+xml"
      class="hidden"
      :disabled="disabled || isUploading || isDeleting"
      @change="handleFileSelect"
    >
    
    <div class="flex items-center gap-6">
      <div 
        class="w-24 h-24 rounded bg-gray-100 flex items-center justify-center border border-gray-300 overflow-hidden"
      >
        <img 
          v-if="previewUrl" 
          :src="previewUrl" 
          alt="Company Logo Preview" 
          class="w-full h-full object-contain"
        />
        <div v-else-if="currentLogoName" class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div v-else class="text-gray-400 text-sm font-medium">No Logo</div>
      </div>
      
      <div class="space-y-3">
        <button
          type="button"
          class="btn btn-secondary text-sm px-4 py-2"
          :disabled="disabled || isUploading || isDeleting"
          @click="triggerSelect"
        >
          {{ isUploading ? 'Uploading...' : (currentLogoName || previewUrl ? 'Change Logo' : 'Upload Logo') }}
        </button>
        
        <button
          v-if="currentLogoName || previewUrl"
          type="button"
          class="block text-sm text-red-600 hover:text-red-700 font-medium"
          :disabled="disabled || isUploading || isDeleting"
          @click="deleteLogo"
        >
          Remove Logo
        </button>
        
        <p class="text-xs text-gray-500 max-w-xs">
          Accepted formats: PNG, JPG, JPEG, SVG. Max size: 2MB.
        </p>
      </div>
    </div>
  </div>
</template>
