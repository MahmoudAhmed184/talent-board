import { computed, ref, shallowRef } from 'vue'
import { defineStore } from 'pinia'
import type { AuthContext, AuthUser, UserRole } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = shallowRef<AuthUser | null>(null)
  const role = ref<UserRole | null>(null)
  const abilities = ref<string[]>([])
  const profile = shallowRef<unknown | null>(null)
  const isSessionLoading = ref(true)

  const isAuthenticated = computed(() => user.value !== null)
  const currentUserContext = computed<AuthContext | null>(() => {
    if (!user.value || !role.value) {
      return null
    }

    return {
      user: user.value,
      role: role.value,
      abilities: abilities.value,
      profile: profile.value,
    }
  })

  function setSession(context: AuthContext) {
    user.value = context.user
    role.value = context.role
    abilities.value = context.abilities
    profile.value = context.profile ?? null
  }

  function clearSession() {
    user.value = null
    role.value = null
    abilities.value = []
    profile.value = null
  }

  function setSessionLoading(value: boolean) {
    isSessionLoading.value = value
  }

  function hasRole(expectedRole: UserRole) {
    return role.value === expectedRole
  }

  function can(ability: string) {
    return abilities.value.includes(ability)
  }

  return {
    abilities,
    can,
    clearSession,
    currentUserContext,
    hasRole,
    isAuthenticated,
    isSessionLoading,
    profile,
    role,
    setSession,
    setSessionLoading,
    user,
  }
})
