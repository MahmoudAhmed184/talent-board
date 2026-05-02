<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { isApiValidationError } from '../../http'
import { useAuth } from '../../features/auth/composables/useAuth'
import type { RegisterPayload, UserRole } from '../../features/auth/types'
import type { ValidationErrorMap } from '../../http'

const router = useRouter()
const { register } = useAuth()

const form = reactive<RegisterPayload>({
  role: 'candidate',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  company_name: '',
})
const errors = ref<ValidationErrorMap>({})
const formError = ref('')
const isSubmitting = ref(false)
const isEmployer = computed(() => form.role === 'employer')

function roleHome(role: UserRole) {
  return role === 'admin' ? '/admin' : `/${role}`
}

function firstError(field: keyof RegisterPayload) {
  return errors.value[field]?.[0] ?? ''
}

async function submit() {
  errors.value = {}
  formError.value = ''
  isSubmitting.value = true

  try {
    const payload: RegisterPayload = {
      role: form.role,
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      company_name: isEmployer.value ? form.company_name : undefined,
    }
    const context = await register(payload)

    await router.push(roleHome(context.role))
  } catch (error) {
    if (isApiValidationError(error)) {
      errors.value = error.errors
      formError.value = error.message
    } else {
      formError.value = 'Unable to create an account right now.'
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
        Register
      </p>
      <h2 class="mt-2 text-2xl font-semibold tracking-normal text-slate-950">
        Create your account
      </h2>
    </div>

    <p
      v-if="formError"
      class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      role="alert"
    >
      {{ formError }}
    </p>

    <fieldset class="grid gap-2">
      <legend class="text-sm font-medium text-slate-700">
        Account type
      </legend>
      <div class="grid grid-cols-2 gap-2 rounded-md bg-slate-100 p-1">
        <label class="cursor-pointer rounded px-3 py-2 text-center text-sm font-medium has-[:checked]:bg-white has-[:checked]:text-emerald-800 has-[:checked]:shadow-sm">
          <input v-model="form.role" type="radio" value="candidate" class="sr-only">
          Candidate
        </label>
        <label class="cursor-pointer rounded px-3 py-2 text-center text-sm font-medium has-[:checked]:bg-white has-[:checked]:text-emerald-800 has-[:checked]:shadow-sm">
          <input v-model="form.role" type="radio" value="employer" class="sr-only">
          Employer
        </label>
      </div>
      <span v-if="firstError('role')" class="text-sm text-red-600">
        {{ firstError('role') }}
      </span>
    </fieldset>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Name</span>
      <input
        v-model="form.name"
        type="text"
        autocomplete="name"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('name') }"
        :aria-invalid="Boolean(firstError('name'))"
      >
      <span v-if="firstError('name')" class="text-sm text-red-600">
        {{ firstError('name') }}
      </span>
    </label>

    <label v-if="isEmployer" class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Company name</span>
      <input
        v-model="form.company_name"
        type="text"
        autocomplete="organization"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('company_name') }"
        :aria-invalid="Boolean(firstError('company_name'))"
      >
      <span v-if="firstError('company_name')" class="text-sm text-red-600">
        {{ firstError('company_name') }}
      </span>
    </label>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Email</span>
      <input
        v-model="form.email"
        type="email"
        autocomplete="email"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('email') }"
        :aria-invalid="Boolean(firstError('email'))"
      >
      <span v-if="firstError('email')" class="text-sm text-red-600">
        {{ firstError('email') }}
      </span>
    </label>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Password</span>
      <input
        v-model="form.password"
        type="password"
        autocomplete="new-password"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('password') }"
        :aria-invalid="Boolean(firstError('password'))"
      >
      <span v-if="firstError('password')" class="text-sm text-red-600">
        {{ firstError('password') }}
      </span>
    </label>

    <label class="grid gap-2">
      <span class="text-sm font-medium text-slate-700">Confirm password</span>
      <input
        v-model="form.password_confirmation"
        type="password"
        autocomplete="new-password"
        class="h-11 rounded-md border border-slate-300 px-3 text-sm text-slate-950 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': firstError('password_confirmation') }"
        :aria-invalid="Boolean(firstError('password_confirmation'))"
      >
      <span v-if="firstError('password_confirmation')" class="text-sm text-red-600">
        {{ firstError('password_confirmation') }}
      </span>
    </label>

    <button
      type="submit"
      class="inline-flex h-11 items-center justify-center rounded-md bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300"
      :disabled="isSubmitting"
    >
      {{ isSubmitting ? 'Creating account...' : 'Create account' }}
    </button>

    <p class="text-sm text-slate-600">
      Already registered?
      <RouterLink to="/auth/login" class="font-semibold text-emerald-700 hover:text-emerald-800">
        Sign in
      </RouterLink>
    </p>
  </form>
</template>
