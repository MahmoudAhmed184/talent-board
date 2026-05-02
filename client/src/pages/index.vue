<script setup lang="ts">
</script>

<template>
  <section class="py-16 sm:py-24">
    <div class="max-w-2xl">
      <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">
        Talent Board
      </p>
      <h1 class="mt-4 text-4xl font-semibold tracking-normal text-slate-950 sm:text-5xl">
        Find work and manage hiring in one focused job board
      </h1>
      <p class="mt-4 text-base leading-7 text-slate-600">
        Browse approved opportunities, sign in to manage your role workspace, and keep
        hiring workflows moving through one SPA.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <RouterLink
          to="/jobs"
          class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        >
          Browse jobs
        </RouterLink>
        <RouterLink
          to="/auth/register"
          class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
        >
          Create account
        </RouterLink>
        <RouterLink
          to="/auth/login"
          class="inline-flex h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        >
          Sign in
        </RouterLink>
      </div>
    </div>
  </section>
</template>
