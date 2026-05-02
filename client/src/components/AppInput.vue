<script setup lang="ts">
import { computed, useId } from 'vue'

type InputType = 'email' | 'file' | 'number' | 'password' | 'search' | 'tel' | 'text' | 'url'

const {
  autocomplete,
  disabled = false,
  error = '',
  helpText = '',
  id,
  label,
  name,
  placeholder = '',
  required = false,
  type = 'text',
} = defineProps<{
  autocomplete?: string
  disabled?: boolean
  error?: string
  helpText?: string
  id?: string
  label: string
  name?: string
  placeholder?: string
  required?: boolean
  type?: InputType
}>()

const emit = defineEmits<{
  change: [event: Event]
}>()

const model = defineModel<string | number | null>({
  default: '',
})
const generatedId = useId()
const inputId = computed(() => id ?? generatedId)
const helpId = computed(() => `${inputId.value}-help`)
const errorId = computed(() => `${inputId.value}-error`)
const describedBy = computed(() => {
  const ids = []

  if (helpText) {
    ids.push(helpId.value)
  }

  if (error) {
    ids.push(errorId.value)
  }

  return ids.length > 0 ? ids.join(' ') : undefined
})

function handleInput(event: Event) {
  if (type === 'file') {
    return
  }

  const target = event.target as HTMLInputElement
  model.value = type === 'number' && target.value !== '' ? target.valueAsNumber : target.value
}

function handleChange(event: Event) {
  emit('change', event)
}
</script>

<template>
  <label class="grid gap-2" :for="inputId">
    <span class="text-sm font-medium text-slate-700">
      {{ label }}
    </span>
    <input
      :id="inputId"
      :name="name"
      :type="type"
      :value="type === 'file' ? undefined : model"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :required="required"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      :aria-describedby="describedBy"
      class="h-11 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-950 outline-none file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-slate-700 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500"
      :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-100': error }"
      @input="handleInput"
      @change="handleChange"
    >
    <span v-if="helpText" :id="helpId" class="text-sm text-slate-500">
      {{ helpText }}
    </span>
    <span v-if="error" :id="errorId" class="text-sm text-red-600">
      {{ error }}
    </span>
  </label>
</template>
