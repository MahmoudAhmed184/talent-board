<script setup lang="ts">
import { computed, nextTick, useId, useTemplateRef, watch } from 'vue'

const {
  ariaLabel,
  closeOnBackdrop = true,
  closeOnEscape = true,
  title = '',
} = defineProps<{
  ariaLabel?: string
  closeOnBackdrop?: boolean
  closeOnEscape?: boolean
  title?: string
}>()

const emit = defineEmits<{
  close: []
}>()

const isOpen = defineModel<boolean>('isOpen', {
  default: false,
})
const panelRef = useTemplateRef<HTMLElement>('panel')
const titleId = useId()
const labelledBy = computed(() => (title ? titleId : undefined))
const dialogLabel = computed(() => (title ? undefined : ariaLabel))

let previouslyFocusedElement: HTMLElement | null = null

const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(',')

watch(isOpen, async (value) => {
  if (value) {
    previouslyFocusedElement = document.activeElement instanceof HTMLElement
      ? document.activeElement
      : null
    await nextTick()
    focusInitialElement()
    return
  }

  previouslyFocusedElement?.focus()
  previouslyFocusedElement = null
})

function getFocusableElements() {
  const panel = panelRef.value

  if (!panel) {
    return []
  }

  return Array.from(panel.querySelectorAll<HTMLElement>(focusableSelector)).filter((element) => (
    element.tabIndex !== -1 && (element.offsetWidth > 0 || element.offsetHeight > 0 || element.getClientRects().length > 0)
  ))
}

function focusInitialElement() {
  const firstFocusableElement = getFocusableElements()[0]
  const target = firstFocusableElement ?? panelRef.value

  target?.focus()
}

function requestClose() {
  isOpen.value = false
  emit('close')
}

function handleBackdropClick() {
  if (closeOnBackdrop) {
    requestClose()
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape' && closeOnEscape) {
    event.preventDefault()
    requestClose()
    return
  }

  if (event.key !== 'Tab') {
    return
  }

  trapFocus(event)
}

function trapFocus(event: KeyboardEvent) {
  const focusableElements = getFocusableElements()

  if (focusableElements.length === 0) {
    event.preventDefault()
    panelRef.value?.focus()
    return
  }

  const firstElement = focusableElements[0]
  const lastElement = focusableElements.at(-1)

  if (event.shiftKey && document.activeElement === firstElement) {
    event.preventDefault()
    lastElement?.focus()
    return
  }

  if (!event.shiftKey && document.activeElement === lastElement) {
    event.preventDefault()
    firstElement.focus()
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 grid place-items-center bg-slate-950/50 p-4"
      @click.self="handleBackdropClick"
      @keydown="handleKeydown"
    >
      <section
        ref="panel"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="labelledBy"
        :aria-label="dialogLabel"
        tabindex="-1"
        class="max-h-[calc(100svh-2rem)] w-full max-w-lg overflow-auto rounded-lg bg-white p-6 text-slate-950 shadow-xl outline-none"
      >
        <div class="flex items-start justify-between gap-4">
          <h2 v-if="title" :id="titleId" class="text-xl font-semibold tracking-normal text-slate-950">
            {{ title }}
          </h2>
          <div v-else />

          <button
            type="button"
            class="inline-flex size-9 items-center justify-center rounded-md text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-200"
            aria-label="Close modal"
            @click="requestClose"
          >
            x
          </button>
        </div>

        <div class="mt-5">
          <slot />
        </div>
      </section>
    </div>
  </Teleport>
</template>
