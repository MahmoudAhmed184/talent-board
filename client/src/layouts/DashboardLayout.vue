<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../features/auth/stores/useAuthStore'

const route = useRoute()
const authStore = useAuthStore()

const navigation = computed(() => {
  if (authStore.role === 'candidate') {
    return [
      { name: 'Overview', href: '/candidate/applications', icon: 'ChartBarIcon' },
      { name: 'My Profile', href: '/candidate/profile', icon: 'UserIcon' },
      { name: 'Browse Jobs', href: '/jobs', icon: 'BriefcaseIcon' },
    ]
  }
  return []
})
</script>

<template>
  <div class="flex min-h-screen bg-slate-950">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 border-r border-slate-800 bg-slate-900 transition-transform md:translate-x-0 md:static md:inset-0">
      <div class="flex h-16 items-center border-b border-slate-800 px-6">
        <span class="text-xl font-bold tracking-tight text-emerald-500">TalentBoard</span>
      </div>
      
      <nav class="mt-6 space-y-1 px-3">
        <RouterLink
          v-for="item in navigation"
          :key="item.name"
          :to="item.href"
          class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all"
          :class="[
            route.path === item.href
              ? 'bg-emerald-600/10 text-emerald-400 shadow-[inset_0_0_12px_rgba(16,185,129,0.05)]'
              : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
          ]"
        >
          {{ item.name }}
        </RouterLink>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto">
      <div class="p-4 md:p-8">
        <slot />
      </div>
    </main>
  </div>
</template>
