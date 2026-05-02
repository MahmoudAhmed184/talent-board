<script setup lang="ts">
</script>

<template>
  <main class="min-h-svh bg-slate-50 text-slate-950">
    <a
      href="#auth-form-card"
      class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-emerald-800"
    >
      Skip to form
    </a>

    <div class="mx-auto grid min-h-svh max-w-6xl items-center gap-10 px-4 py-8 sm:py-10 md:grid-cols-[1fr_28rem] md:px-6">
      <section class="max-w-xl">
        <RouterLink to="/" class="text-xl font-semibold text-slate-950">
          Talent Board
        </RouterLink>
        <h1 class="mt-6 text-3xl font-semibold tracking-normal text-slate-950 sm:mt-8 sm:text-4xl">
          Access your hiring workspace
        </h1>
        <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base">
          Sign in or create an account to manage jobs, applications, and profile details.
        </p>
      </section>

      <section id="auth-form-card" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <slot />
      </section>
    </div>
  </main>
</template>
