<route lang="json">
{
  "meta": {
    "guestOnly": true
  }
}
</route>

<script setup lang="ts">
import AuthLayout from '../../layouts/AuthLayout.vue'
</script>

<template>
  <AuthLayout>
    <RouterView />
  </AuthLayout>
</template>
