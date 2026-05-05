import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { useCandidateProfile } from '../composables/useCandidateProfile'
import type { CandidateProfile, UpdateCandidateProfilePayload } from '../types'

export const useCandidateProfileStore = defineStore('candidate-profile', () => {
  const profile = ref<CandidateProfile | null>(null)
  const { fetchProfile, updateProfile, isFetching, isUpdating } = useCandidateProfile()

  const defaultResumeId = computed(() => profile.value?.default_resume_id ?? null)
  const defaultResume = computed(() => profile.value?.default_resume ?? null)

  async function loadProfile() {
    profile.value = await fetchProfile()
  }

  async function saveProfile(payload: UpdateCandidateProfilePayload) {
    profile.value = await updateProfile(payload)
  }

  return {
    defaultResume,
    defaultResumeId,
    isFetching,
    isUpdating,
    loadProfile,
    profile,
    saveProfile,
  }
})
