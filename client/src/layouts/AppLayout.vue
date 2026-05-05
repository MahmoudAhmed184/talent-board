<script setup lang="ts">
import { computed } from 'vue'
import ToastHost from '../components/ToastHost.vue'
import { useAuthStore } from '../features/auth/stores/useAuthStore'

const authStore = useAuthStore()
const dashboardPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate/applications'
  }

  if (authStore.role === 'employer') {
    return '/employer/dashboard'
  }

  if (authStore.role === 'admin') {
    return '/admin'
  }

  return '/'
})
</script>

<template>
  <div class="min-h-svh bg-slate-50 text-slate-950">
    <ToastHost />

    <div
      v-if="authStore.isSessionLoading"
      class="h-1 w-full overflow-hidden bg-slate-200"
      role="status"
      aria-label="Restoring session"
    >
      <div class="h-full w-1/3 animate-pulse bg-emerald-600" />
    </div>

    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur">
      <nav
        class="mx-auto flex min-h-16 max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 md:px-6"
        aria-label="Public navigation"
      >
        <RouterLink to="/" class="text-lg font-semibold text-slate-950 hover:text-emerald-700">
          Talent Board
        </RouterLink>

        <div class="flex items-center gap-2">
          <RouterLink
            to="/jobs"
            class="rounded-md px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-950"
            active-class="bg-emerald-50 text-emerald-800"
          >
            Jobs
          </RouterLink>
          <RouterLink
            v-if="authStore.isAuthenticated"
            :to="dashboardPath"
            class="rounded-md px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-950"
            active-class="bg-emerald-50 text-emerald-800"
          >
            Dashboard
          </RouterLink>
          <RouterLink
            v-if="authStore.role === 'candidate'"
            to="/candidate/profile"
            class="rounded-md px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-950"
            active-class="bg-emerald-50 text-emerald-800"
          >
            Profile
          </RouterLink>
          <span
            v-else-if="authStore.isSessionLoading"
            class="rounded-md px-3 py-2 text-sm font-semibold text-slate-500"
            aria-live="polite"
          >
            Checking session
          </span>
          <template v-else>
            <RouterLink
              to="/auth/login"
              class="rounded-md px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-slate-950"
              active-class="bg-emerald-50 text-emerald-800"
            >
              Sign in
            </RouterLink>
            <RouterLink
              to="/auth/register"
              class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-700 px-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"
            >
              Register
            </RouterLink>
          </template>
        </div>
      </nav>
    </header>

    <main class="mx-auto min-h-[calc(100svh-4rem)] max-w-7xl px-4 md:px-6">
      <RouterView v-slot="{ Component }">
        <Suspense>
          <component :is="Component" />
          <template #fallback>
            <div class="py-16 text-sm font-medium text-slate-600" role="status">
              Loading content...
            </div>
          </template>
        </Suspense>
      </RouterView>
    </main>
  </div>
</template>
