<script setup lang="ts">
import { computed } from 'vue'

type ButtonVariant = 'primary' | 'secondary' | 'danger' | 'ghost'
type ButtonType = 'button' | 'submit' | 'reset'

const {
  disabled = false,
  loading = false,
  type = 'button',
  variant = 'primary',
} = defineProps<{
  disabled?: boolean
  loading?: boolean
  type?: ButtonType
  variant?: ButtonVariant
}>()

const variantClasses: Record<ButtonVariant, string> = {
  danger: 'bg-red-700 text-white hover:bg-red-800 focus-visible:ring-red-200',
  ghost: 'bg-transparent text-slate-700 hover:bg-slate-100 focus-visible:ring-slate-200',
  primary: 'bg-emerald-700 text-white hover:bg-emerald-800 focus-visible:ring-emerald-200',
  secondary: 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 focus-visible:ring-slate-200',
}

const isDisabled = computed(() => disabled || loading)
const buttonClasses = computed(() => [
  'inline-flex h-11 min-w-24 items-center justify-center gap-2 rounded-md px-4 text-sm font-semibold outline-none transition focus-visible:ring-4 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-300 disabled:text-slate-500',
  variantClasses[variant],
])
</script>

<template>
  <button
    :type="type"
    :disabled="isDisabled"
    :aria-busy="loading"
    :class="buttonClasses"
  >
    <span
      v-if="loading"
      class="size-4 animate-spin rounded-full border-2 border-current border-r-transparent"
      aria-hidden="true"
    />
    <span class="truncate">
      <slot />
    </span>
  </button>
</template>
