<route lang="json">
{
  "meta": {
    "layout": "standalone",
    "requiresAuth": true,
    "role": "candidate"
  }
}
</route>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../features/auth/composables/useAuth'
import {
  LayoutDashboard,
  FileText,
  User,
  Briefcase,
  LogOut,
  Menu,
  X
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { logout } = useAuth()

const isSidebarOpen = ref(false)

const navItems = [
  { label: 'Overview', to: '/candidate', icon: LayoutDashboard },
  { label: 'Applications', to: '/candidate/applications', icon: FileText },
  { label: 'Profile', to: '/candidate/profile', icon: User },
  { label: 'Browse Jobs', to: '/candidate/jobs', icon: Briefcase },
]

async function handleLogout() {
  try {
    await logout()
  } catch {
    // Ensure local session is cleared even if the API call fails
  }
  router.push('/')
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Mobile sidebar backdrop -->
    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
      @click="isSidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-sm"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo area -->
      <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
        <RouterLink to="/" class="flex items-center gap-2 text-xl font-bold text-slate-900 tracking-tight">
          <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
            <span class="text-white text-lg leading-none select-none">T</span>
          </div>
          TalentBoard
        </RouterLink>
        <button class="lg:hidden text-slate-400 hover:text-slate-600" @click="isSidebarOpen = false">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation -->
      <div class="flex-1 overflow-y-auto py-6 px-4 flex flex-col gap-1">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group"
          :class="[
            route.path === item.to || (item.to !== '/candidate' && route.path.startsWith(item.to))
              ? 'bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-100/50'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          ]"
          @click="isSidebarOpen = false"
        >
          <component
            :is="item.icon"
            class="w-5 h-5 transition-colors"
            :class="[
              route.path === item.to || (item.to !== '/candidate' && route.path.startsWith(item.to))
                ? 'text-emerald-600'
                : 'text-slate-400 group-hover:text-slate-600'
            ]"
          />
          {{ item.label }}
        </RouterLink>
      </div>

      <!-- Logout -->
      <div class="p-4 border-t border-slate-100 mt-auto">
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group"
        >
          <LogOut class="w-5 h-5 text-slate-400 group-hover:text-red-500 transition-colors" />
          Sign out
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 min-w-0 flex flex-col min-h-screen">
      <!-- Mobile Header -->
      <header class="h-16 lg:hidden flex items-center justify-between px-4 border-b border-slate-200 bg-white shadow-sm shrink-0">
        <div class="flex items-center gap-2">
           <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
            <span class="text-white text-sm font-bold leading-none select-none">T</span>
          </div>
          <span class="font-bold text-slate-900">TalentBoard</span>
        </div>
        <button @click="isSidebarOpen = true" class="p-2 -mr-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
          <Menu class="w-6 h-6" />
        </button>
      </header>

      <!-- Page Content -->
      <div class="flex-1 overflow-auto">
        <div class="max-w-5xl mx-auto p-4 md:p-8">
          <RouterView v-slot="{ Component, route }">
            <Suspense>
              <component :is="Component" :key="route.path" />
              <template #fallback>
                <div class="flex items-center justify-center py-32">
                  <div class="size-12 rounded-full border-4 border-slate-200 border-t-emerald-600 animate-spin" />
                </div>
              </template>
            </Suspense>
          </RouterView>
        </div>
      </div>
    </main>
  </div>
</template>
