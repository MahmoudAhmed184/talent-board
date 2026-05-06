<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import ToastHost from '../components/ToastHost.vue'
import { useAuthStore } from '../features/auth/stores/useAuthStore'
import { useAuth } from '../features/auth/composables/useAuth'
import { useRouter } from 'vue-router'
import { LogOut } from 'lucide-vue-next'

const router = useRouter()
const { logout } = useAuth()

const authStore = useAuthStore()
const isMenuOpen = ref(false)
const menuRef = ref<HTMLElement | null>(null)

const handleLogout = async () => {
  isMenuOpen.value = false
  try {
    await logout()
  } catch {
    // Ensure local session is cleared even if the API call fails
  }
  router.push('/')
}

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function handleClickOutside(event: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(event.target as Node)) {
    isMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside, true)
})

const dashboardPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate'
  }

  if (authStore.role === 'employer') {
    return '/employer/dashboard'
  }

  if (authStore.role === 'admin') {
    return '/admin'
  }

  return '/'
})

const jobsPath = computed(() => {
  if (authStore.role === 'candidate') {
    return '/candidate/jobs'
  }
  return '/jobs'
})
</script>

<template>
  <div class="min-h-svh bg-slate-50 text-slate-900">
    <ToastHost />

    <div
      v-if="authStore.isSessionLoading"
      class="h-1 w-full overflow-hidden bg-slate-200"
      role="status"
      aria-label="Restoring session"
    >
      <div class="h-full w-1/3 animate-pulse bg-emerald-600" />
    </div>

    <!-- Top Navbar Light Theme -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm backdrop-blur-xl">
      <nav
        class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 md:px-8"
        aria-label="Global"
      >
        <RouterLink to="/" class="group flex items-center gap-2">
          <div class="size-9 rounded-xl bg-emerald-600 shadow-sm shadow-emerald-500/20 flex items-center justify-center text-white font-bold text-xl">T</div>
          <span class="text-xl font-bold tracking-tight text-slate-900 group-hover:text-emerald-700 transition-colors">TalentBoard</span>
        </RouterLink>

        <div class="flex items-center gap-6">
          <RouterLink
            :to="jobsPath"
            class="text-sm font-medium text-slate-600 hover:text-emerald-700 transition-colors"
            active-class="text-emerald-600 font-semibold"
          >
            Find Jobs
          </RouterLink>
          
          <template v-if="authStore.isAuthenticated">
            <RouterLink
              :to="dashboardPath"
              class="text-sm font-medium text-slate-600 hover:text-emerald-700 transition-colors"
              active-class="text-emerald-600 font-semibold"
            >
              Dashboard
            </RouterLink>

            <div class="h-6 w-px bg-slate-200" />
            
            <div class="flex items-center gap-4">
              <div class="hidden md:flex flex-col items-end">
                <span class="text-sm font-semibold text-slate-900">{{ authStore.user?.name }}</span>
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">{{ authStore.role }}</span>
              </div>
              
              <div ref="menuRef" class="relative">
                <button
                  id="user-menu-button"
                  @click="toggleMenu"
                  class="size-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center hover:border-emerald-500 transition-all overflow-hidden text-emerald-700 font-bold"
                  :aria-expanded="isMenuOpen"
                  aria-haspopup="true"
                >
                  {{ authStore.user?.name?.[0] }}
                </button>
                
                <!-- Dropdown -->
                <div
                  v-show="isMenuOpen"
                  class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white border border-slate-100 shadow-xl py-1 z-50"
                  role="menu"
                  aria-labelledby="user-menu-button"
                >
                  <RouterLink :to="dashboardPath" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900" role="menuitem" @click="isMenuOpen = false">Dashboard</RouterLink>
                  <RouterLink v-if="authStore.role === 'candidate'" to="/candidate/profile" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900" role="menuitem" @click="isMenuOpen = false">My Profile</RouterLink>
                  <div class="h-px bg-slate-100 my-1" />
                  <button @click="handleLogout" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2" role="menuitem">
                    <LogOut class="w-4 h-4" />
                    Sign Out
                  </button>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="!authStore.isSessionLoading">
            <RouterLink
              to="/auth/login"
              class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"
            >
              Log in
            </RouterLink>
            <RouterLink
              to="/auth/register"
              class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all active:scale-95"
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
              <div class="size-12 rounded-full border-4 border-slate-200 border-t-emerald-600 animate-spin" />
            </div>
          </template>
        </Suspense>
      </RouterView>
    </main>
  </div>
</template>

