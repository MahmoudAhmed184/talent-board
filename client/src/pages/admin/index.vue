<script setup lang="ts">
const cards = [
  {
    title: 'Moderation queue',
    body: 'Review pending job listings and make approve or reject decisions.',
    to: '/admin/jobs',
  },
  {
    title: 'Platform activity',
    body: 'Track users, jobs, and recent applications from the D2 activity endpoint.',
    to: '/admin/jobs',
  },
] as const
</script>

<template>
  <section class="grid gap-6">
    <header class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-semibold uppercase tracking-wider text-violet-700">
        Admin
      </p>
      <h1 class="mt-3 text-2xl font-semibold tracking-normal text-slate-950">
        Governance workspace
      </h1>
      <p class="mt-2 text-sm leading-6 text-slate-600">
        This area is now wired for moderation and platform activity, with the main D2 workflow under jobs.
      </p>
    </header>

    <div class="grid gap-4 md:grid-cols-2">
      <article
        v-for="card in cards"
        :key="card.title"
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
      >
        <h2 class="text-lg font-semibold text-slate-950">{{ card.title }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ card.body }}</p>
        <RouterLink
          :to="card.to"
          class="mt-4 inline-flex h-10 items-center justify-center rounded-md bg-violet-700 px-4 text-sm font-semibold text-white hover:bg-violet-800"
        >
          Open workspace
        </RouterLink>
      </article>
    </div>
  </section>
</template>
