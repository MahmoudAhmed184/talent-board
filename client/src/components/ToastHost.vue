<script setup lang="ts">
import { useToast } from '../composables/useToast'

const { removeToast, toasts } = useToast()
</script>

<template>
  <Teleport to="body">
    <div class="fixed right-4 top-4 z-50 grid w-[min(24rem,calc(100vw-2rem))] gap-3" aria-live="polite">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="rounded-lg border bg-white p-4 shadow-lg"
        :class="toast.type === 'success' ? 'border-emerald-200' : 'border-red-200'"
        :role="toast.type === 'error' ? 'alert' : 'status'"
      >
        <div class="flex items-start gap-3">
          <div
            class="mt-1 size-2 shrink-0 rounded-full"
            :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
            aria-hidden="true"
          />

          <div class="min-w-0 flex-1">
            <p
              v-if="toast.title"
              class="text-sm font-semibold text-slate-950"
            >
              {{ toast.title }}
            </p>
            <p class="text-sm leading-6 text-slate-600">
              {{ toast.message }}
            </p>
          </div>

          <button
            type="button"
            class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200"
            aria-label="Dismiss notification"
            @click="removeToast(toast.id)"
          >
            x
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
