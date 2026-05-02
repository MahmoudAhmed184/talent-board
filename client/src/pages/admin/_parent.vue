<route lang="json">
{
  "meta": {
    "layout": "standalone",
    "requiresAuth": true,
    "role": "admin"
  }
}
</route>

<script setup lang="ts">
const navItems = [
  { label: 'Overview', to: '/admin' },
  { label: 'Job moderation', to: '/admin/jobs' },
] as const
</script>

<template>
  <main class="min-h-svh bg-slate-50 text-slate-950">
    <div class="mx-auto grid min-h-svh max-w-7xl gap-6 px-4 py-6 md:grid-cols-[16rem_1fr] md:px-6">
      <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <RouterLink to="/" class="text-lg font-semibold text-slate-950">
          Talent Board
        </RouterLink>

        <nav aria-label="Admin navigation" class="mt-8 grid gap-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-950"
            active-class="bg-violet-50 text-violet-800"
          >
            {{ item.label }}
          </RouterLink>
        </nav>
      </aside>

      <section class="min-w-0">
        <RouterView />
      </section>
    </div>
  </main>
</template>
