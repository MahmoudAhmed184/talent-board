<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { isApiValidationError } from '../../http'
import { useAuth } from '../../features/auth/composables/useAuth'
import type { LoginPayload, UserRole } from '../../features/auth/types'
import { useFormErrors, type ApiErrorPayload } from '../../composables/useFormErrors'

const route = useRoute()
const router = useRouter()
const { login } = useAuth()

const form = reactive<LoginPayload>({
  email: '',
  password: '',
})
const isSubmitting = ref(false)
const { state, clearErrors, setFieldError, setFormError, mapApiErrors, getFieldError } =
  useFormErrors<keyof LoginPayload>()

function roleHome(role: UserRole) {
  if (role === 'candidate') {
    return '/'
  }

  if (role === 'employer') {
    return '/employer/dashboard'
  }

  return role === 'admin' ? '/admin' : `/${role}`
}

function firstError(field: keyof LoginPayload) {
  return getFieldError(field) ?? ''
}

function redirectTarget(role: UserRole) {
  return typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
    ? route.query.redirect
    : roleHome(role)
}

async function submit() {
  clearErrors()

  if (!form.email.trim()) {
    setFieldError('email', 'Email is required.')
  }
  if (!form.password.trim()) {
    setFieldError('password', 'Password is required.')
  }
  if (firstError('email') || firstError('password')) {
    return
  }

  isSubmitting.value = true

  try {
    const context = await login(form)
    await router.push(redirectTarget(context.role))
  } catch (error) {
    if (isApiValidationError(error)) {
      mapApiErrors(error as ApiErrorPayload)
    } else {
      setFormError('Unable to sign in with those credentials.')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <form class="grid gap-5" novalidate @submit.prevent="submit">
    <div>
      <p class="text-sm font-semibold uppercase tracking-wider text-emerald-700">
        Sign in
      </p>
      <h2 class="mt-2 text-2xl font-semibold tracking-normal text-slate-950">
        Welcome back
      </h2>
    </div>

    <p
      v-if="state.formError.value"
      class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      role="alert"
      aria-live="assertive"
    >
      {{ state.formError.value }}
    </p>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Email</span>
      <input
        v-model="form.email"
        type="email"
        autocomplete="email"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('email') }"
        :aria-invalid="Boolean(firstError('email'))"
        :aria-describedby="firstError('email') ? 'login-email-error' : undefined"
      >
      <span v-if="firstError('email')" id="login-email-error" class="text-sm text-red-600" role="alert">
        {{ firstError('email') }}
      </span>
    </label>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Password</span>
      <input
        v-model="form.password"
        type="password"
        autocomplete="current-password"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('password') }"
        :aria-invalid="Boolean(firstError('password'))"
        :aria-describedby="firstError('password') ? 'login-password-error' : undefined"
      >
      <span
        v-if="firstError('password')"
        id="login-password-error"
        class="text-sm text-red-600"
        role="alert"
      >
        {{ firstError('password') }}
      </span>
    </label>

    <button
      type="submit"
      class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
      :disabled="isSubmitting"
    >
      {{ isSubmitting ? 'Signing in...' : 'Sign in' }}
    </button>

    <p class="text-sm text-slate-600">
      Need an account?
      <RouterLink to="/auth/register" class="font-semibold text-emerald-700 hover:text-emerald-800">
        Create one
      </RouterLink>
    </p>
  </form>
</template>
