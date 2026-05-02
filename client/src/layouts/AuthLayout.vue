<script setup lang="ts">
</script>

<template>
  <main class="min-h-svh bg-slate-50 text-slate-950">
    <div class="mx-auto grid min-h-svh max-w-6xl items-center gap-10 px-4 py-8 md:grid-cols-[1fr_28rem] md:px-6">
      <section class="max-w-xl">
        <RouterLink to="/" class="text-xl font-semibold text-slate-950">
          Talent Board
        </RouterLink>
        <h1 class="mt-8 text-4xl font-semibold tracking-normal text-slate-950">
          Access your hiring workspace
        </h1>
        <p class="mt-4 text-base leading-7 text-slate-600">
          Sign in or create an account to manage jobs, applications, and profile details.
        </p>
      </section>

      <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <slot />
      </section>
    </div>
  </main>
</template>
