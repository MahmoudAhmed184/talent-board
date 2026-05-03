<template>
  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">
      Candidate
    </p>
    <h1 class="mt-3 text-2xl font-semibold tracking-normal text-slate-950">
      Candidate workspace
    </h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">
      Candidate routes render inside the protected candidate layout shell.
    </p>
    <div class="mt-5">
      <RouterLink
        to="/candidate/applications"
        class="inline-flex h-10 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800"
      >
        Track applications
      </RouterLink>
    </div>
  </section>
</template>
