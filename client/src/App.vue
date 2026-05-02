<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from './layouts/AppLayout.vue'

const route = useRoute()
const usesStandaloneLayout = computed(() => route.meta.layout === 'standalone')
</script>

<template>
  <RouterView v-if="usesStandaloneLayout" />
  <AppLayout v-else />
</template>
