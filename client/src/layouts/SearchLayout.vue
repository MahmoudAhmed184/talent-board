<script setup lang="ts">
// SearchLayout provides a two-column layout for the job search page
</script>

<template>
  <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
    <slot name="sidebar" />
    <main>
      <slot />
    </main>
  </div>
</template>
