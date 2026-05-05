<script setup lang="ts">
import { computed } from 'vue'
import ToastHost from '../components/ToastHost.vue'
import { useAuthStore } from '../features/auth/stores/useAuthStore'

const authStore = useAuthStore()
const dashboardPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate/dashboard'
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

    <header class="sticky top-0 z-40 border-b border-white/5 bg-slate-950/80 backdrop-blur-xl">
      <nav
        class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 md:px-8"
        aria-label="Global"
      >
        <RouterLink to="/" class="group flex items-center gap-2">
          <div class="size-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 shadow-lg shadow-emerald-500/20 flex items-center justify-center text-white font-bold text-xl">T</div>
          <span class="text-xl font-bold tracking-tight text-white group-hover:text-emerald-400 transition-colors">TalentBoard</span>
        </RouterLink>

        <div class="flex items-center gap-6">
          <RouterLink
            to="/jobs"
            class="text-sm font-medium text-slate-400 hover:text-white transition-colors"
            active-class="text-emerald-400"
          >
            Find Jobs
          </RouterLink>
          
          <template v-if="authStore.isAuthenticated">
            <RouterLink
              :to="dashboardPath"
              class="text-sm font-medium text-slate-400 hover:text-white transition-colors"
              active-class="text-emerald-400"
            >
              Dashboard
            </RouterLink>

            <div class="h-6 w-px bg-white/10" />
            
            <div class="flex items-center gap-4">
              <div class="hidden md:flex flex-col items-end">
                <span class="text-sm font-semibold text-white">{{ authStore.user?.name }}</span>
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">{{ authStore.role }}</span>
              </div>
              
              <div class="relative group">
                <button class="size-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center hover:border-emerald-500/50 transition-all overflow-hidden">
                  <div class="text-emerald-400 font-bold">{{ authStore.user?.name?.[0] }}</div>
                </button>
                
                <!-- Dropdown -->
                <div class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-slate-900 border border-white/10 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-1">
                  <RouterLink :to="dashboardPath" class="block px-4 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white">Dashboard</RouterLink>
                  <RouterLink v-if="authStore.role === 'candidate'" to="/candidate/profile" class="block px-4 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white">My Profile</RouterLink>
                  <div class="h-px bg-white/5 my-1" />
                  <button @click="authStore.logout()" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-red-500/10">Sign Out</button>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="!authStore.isSessionLoading">
            <RouterLink
              to="/auth/login"
              class="text-sm font-medium text-slate-400 hover:text-white transition-colors"
            >
              Log in
            </RouterLink>
            <RouterLink
              to="/auth/register"
              class="inline-flex h-10 items-center justify-center rounded-full bg-emerald-600 px-6 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-500 transition-all active:scale-95"
            >
              Join Now
            </RouterLink>
          </template>
        </div>
      </nav>
    </header>

    <main class="min-h-[calc(100svh-5rem)]">
      <RouterView v-slot="{ Component }">
        <Suspense>
          <component :is="Component" />
          <template #fallback>
            <div class="flex items-center justify-center py-32">
              <div class="size-12 rounded-full border-4 border-slate-800 border-t-emerald-500 animate-spin" />
            </div>
          </template>
        </Suspense>
      </RouterView>
    </main>
  </div>
</template>
