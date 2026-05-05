<script setup lang="ts">
import { ref } from 'vue'
import { http } from '../../../http'
import type { Resume } from '../types'

const props = defineProps<{
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'uploaded', resume: Resume): void
  (e: 'error', message: string): void
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const isUploading = ref(false)
const uploadProgress = ref(0)

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  uploadFile(file)
  
  // Reset input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

async function uploadFile(file: File) {
  isUploading.value = true
  uploadProgress.value = 0
  
  const formData = new FormData()
  formData.append('file', file)
  
  try {
    const response = await http.post<{ data: Resume }>('/api/v1/candidate/resumes', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      },
    })
    
    emit('uploaded', response.data.data)
  } catch (error: any) {
    const message = error.response?.data?.message || 'Failed to upload resume.'
    emit('error', message)
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

function triggerSelect() {
  fileInput.value?.click()
}
</script>

<template>
  <div class="resume-upload">
    <input
      ref="fileInput"
      type="file"
      accept=".pdf,.doc,.docx"
      class="hidden"
      :disabled="disabled || isUploading"
      @change="handleFileSelect"
    >
    
    <button
      type="button"
      class="btn btn-secondary w-full"
      :disabled="disabled || isUploading"
      @click="triggerSelect"
    >
      <span v-if="isUploading">Uploading... {{ uploadProgress }}%</span>
      <span v-else>Upload New Resume</span>
    </button>
    <p class="text-xs text-gray-500 mt-2">Accepted formats: PDF, DOC, DOCX. Max size: 10MB.</p>
  </div>
</template>
