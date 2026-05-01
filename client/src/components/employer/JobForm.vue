<script setup lang="ts">
import { computed } from 'vue';
import {
  useEmployerJobForm,
  type EmployerJobFormData,
  type EmployerJobPayload,
} from '../../composables/useEmployerJobForm';

const {
  mode = 'create',
  submitLabel = 'Save job',
  initialValues = {},
} = defineProps<{
  mode?: 'create' | 'edit';
  submitLabel?: string;
  initialValues?: Partial<EmployerJobFormData>;
}>();

const emit = defineEmits<{
  submit: [payload: EmployerJobPayload];
  cancel: [];
}>();

const form = useEmployerJobForm({
  initialValues,
  onSubmit: async (payload) => {
    emit('submit', payload);
  },
});

const heading = computed(() =>
  mode === 'edit' ? 'Edit job listing' : 'Create job listing',
);
const formDescription = computed(() =>
  mode === 'edit'
    ? 'Update your listing details and save your changes.'
    : 'Complete all required fields before publishing for review.',
);

const submitForm = async (): Promise<void> => {
  await form.submit();
};
const formIsSubmitting = computed(() => form.isSubmitting.value);
</script>

<template>
  <section class="job-form-shell" aria-labelledby="job-form-title">
    <header class="job-form-header">
      <h1 id="job-form-title">{{ heading }}</h1>
      <p>{{ formDescription }}</p>
    </header>

    <form class="job-form" @submit.prevent="submitForm" novalidate>
      <p
        v-if="form.getFormError()"
        class="form-alert"
        role="alert"
        aria-live="assertive"
      >
        {{ form.getFormError() }}
      </p>

      <div class="field">
        <label for="job-title">Job title</label>
        <input
          id="job-title"
          v-model="form.values.title"
          type="text"
          autocomplete="organization-title"
          :aria-invalid="Boolean(form.getFieldError('title'))"
          :aria-describedby="form.getFieldError('title') ? 'job-title-error' : undefined"
        />
        <p
          v-if="form.getFieldError('title')"
          id="job-title-error"
          class="field-error"
          role="alert"
        >
          {{ form.getFieldError('title') }}
        </p>
      </div>

      <div class="field">
        <label for="job-description">Description</label>
        <textarea
          id="job-description"
          v-model="form.values.description"
          rows="5"
          :aria-invalid="Boolean(form.getFieldError('description'))"
          :aria-describedby="
            form.getFieldError('description') ? 'job-description-error' : undefined
          "
        />
        <p
          v-if="form.getFieldError('description')"
          id="job-description-error"
          class="field-error"
          role="alert"
        >
          {{ form.getFieldError('description') }}
        </p>
      </div>

      <div class="field-grid">
        <div class="field">
          <label for="job-location">Location</label>
          <input
            id="job-location"
            v-model="form.values.location"
            type="text"
            :aria-invalid="Boolean(form.getFieldError('location'))"
            :aria-describedby="
              form.getFieldError('location') ? 'job-location-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('location')"
            id="job-location-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('location') }}
          </p>
        </div>

        <div class="field">
          <label for="job-category">Category</label>
          <input
            id="job-category"
            v-model="form.values.category"
            type="text"
            :aria-invalid="Boolean(form.getFieldError('category'))"
            :aria-describedby="
              form.getFieldError('category') ? 'job-category-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('category')"
            id="job-category-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('category') }}
          </p>
        </div>
      </div>

      <div class="field-grid">
        <div class="field">
          <label for="job-work-type">Work type</label>
          <select
            id="job-work-type"
            v-model="form.values.workType"
            :aria-invalid="Boolean(form.getFieldError('workType'))"
            :aria-describedby="
              form.getFieldError('workType') ? 'job-work-type-error' : undefined
            "
          >
            <option value="">Select work type</option>
            <option value="remote">Remote</option>
            <option value="on-site">On-site</option>
            <option value="hybrid">Hybrid</option>
          </select>
          <p
            v-if="form.getFieldError('workType')"
            id="job-work-type-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('workType') }}
          </p>
        </div>

        <div class="field">
          <label for="job-experience-level">Experience level</label>
          <select
            id="job-experience-level"
            v-model="form.values.experienceLevel"
            :aria-invalid="Boolean(form.getFieldError('experienceLevel'))"
            :aria-describedby="
              form.getFieldError('experienceLevel')
                ? 'job-experience-level-error'
                : undefined
            "
          >
            <option value="">Select experience level</option>
            <option value="junior">Junior</option>
            <option value="mid">Mid</option>
            <option value="senior">Senior</option>
            <option value="lead">Lead</option>
          </select>
          <p
            v-if="form.getFieldError('experienceLevel')"
            id="job-experience-level-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('experienceLevel') }}
          </p>
        </div>
      </div>

      <div class="field-grid">
        <div class="field">
          <label for="salary-min">Salary min</label>
          <input
            id="salary-min"
            v-model="form.values.salaryMin"
            type="number"
            min="0"
            step="1"
          />
        </div>

        <div class="field">
          <label for="salary-max">Salary max</label>
          <input
            id="salary-max"
            v-model="form.values.salaryMax"
            type="number"
            min="0"
            step="1"
            :aria-invalid="Boolean(form.getFieldError('salaryMax'))"
            :aria-describedby="
              form.getFieldError('salaryMax') ? 'salary-max-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('salaryMax')"
            id="salary-max-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('salaryMax') }}
          </p>
        </div>
      </div>

      <div class="actions">
        <button
          type="button"
          class="button-secondary"
          @click="
            form.reset();
            emit('cancel');
          "
        >
          Reset
        </button>
        <button type="submit" class="button-primary" :disabled="formIsSubmitting">
          {{ formIsSubmitting ? 'Saving...' : submitLabel }}
        </button>
      </div>
    </form>
  </section>
</template>
