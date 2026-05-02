import { readonly, ref } from 'vue'

export type ToastType = 'error' | 'success'

export interface ToastMessage {
  id: number
  message: string
  title?: string
  type: ToastType
}

interface ToastOptions {
  timeout?: number
  title?: string
}

const DEFAULT_TIMEOUT = 5000
const toasts = ref<ToastMessage[]>([])
const timers = new Map<number, ReturnType<typeof window.setTimeout>>()
let nextToastId = 1

function removeToast(id: number) {
  const timer = timers.get(id)

  if (timer) {
    window.clearTimeout(timer)
    timers.delete(id)
  }

  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

function pushToast(type: ToastType, message: string, options: ToastOptions = {}) {
  const id = nextToastId
  nextToastId += 1

  const toast: ToastMessage = {
    id,
    message,
    title: options.title,
    type,
  }

  toasts.value = [...toasts.value, toast]

  if (options.timeout !== 0) {
    const timeout = options.timeout ?? DEFAULT_TIMEOUT
    timers.set(id, window.setTimeout(() => removeToast(id), timeout))
  }

  return id
}

function clearToasts() {
  for (const timer of timers.values()) {
    window.clearTimeout(timer)
  }

  timers.clear()
  toasts.value = []
}

export function useToast() {
  return {
    clearToasts,
    error: (message: string, options?: ToastOptions) => pushToast('error', message, options),
    removeToast,
    success: (message: string, options?: ToastOptions) => pushToast('success', message, options),
    toasts: readonly(toasts),
  }
}
