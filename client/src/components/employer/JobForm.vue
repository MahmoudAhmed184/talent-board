<script setup lang="ts">
import { computed, watch } from 'vue';
import {
  useEmployerJobForm,
  type EmployerJobFormData,
  type EmployerJobPayload,
} from '../../composables/useEmployerJobForm';

const {
  mode = 'create',
  submitLabel = 'Save job',
  initialValues = {},
  submitHandler,
} = defineProps<{
  mode?: 'create' | 'edit';
  submitLabel?: string;
  initialValues?: Partial<EmployerJobFormData>;
  submitHandler?: (payload: EmployerJobPayload) => Promise<void>;
}>();

const emit = defineEmits<{
  submit: [payload: EmployerJobPayload];
  cancel: [];
}>();

const form = useEmployerJobForm({
  initialValues,
  onSubmit: async (payload) => {
    if (submitHandler) {
      await submitHandler(payload);
      return;
    }

    emit('submit', payload);
  },
});

watch(
  () => initialValues,
  (nextValues) => {
    form.setValues(nextValues);
  },
  { deep: true },
);

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
          <label for="job-responsibilities">Responsibilities</label>
          <textarea
            id="job-responsibilities"
            v-model="form.values.responsibilities"
            rows="4"
            :aria-invalid="Boolean(form.getFieldError('responsibilities'))"
            :aria-describedby="
              form.getFieldError('responsibilities') ? 'job-responsibilities-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('responsibilities')"
            id="job-responsibilities-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('responsibilities') }}
          </p>
        </div>

        <div class="field">
          <label for="job-qualifications">Qualifications</label>
          <textarea
            id="job-qualifications"
            v-model="form.values.qualifications"
            rows="4"
            :aria-invalid="Boolean(form.getFieldError('qualifications'))"
            :aria-describedby="
              form.getFieldError('qualifications') ? 'job-qualifications-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('qualifications')"
            id="job-qualifications-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('qualifications') }}
          </p>
        </div>
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
            :aria-invalid="Boolean(form.getFieldError('salaryMin'))"
            :aria-describedby="
              form.getFieldError('salaryMin') ? 'salary-min-error' : undefined
            "
          />
          <p
            v-if="form.getFieldError('salaryMin')"
            id="salary-min-error"
            class="field-error"
            role="alert"
          >
            {{ form.getFieldError('salaryMin') }}
          </p>
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

      <div class="field">
        <label for="job-expires-at">Expiration date</label>
        <input
          id="job-expires-at"
          v-model="form.values.expiresAt"
          type="date"
          :aria-invalid="Boolean(form.getFieldError('expiresAt'))"
          :aria-describedby="form.getFieldError('expiresAt') ? 'job-expires-at-error' : undefined"
        />
        <p
          v-if="form.getFieldError('expiresAt')"
          id="job-expires-at-error"
          class="field-error"
          role="alert"
        >
          {{ form.getFieldError('expiresAt') }}
        </p>
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

<style scoped>
.job-form-shell {
  display: grid;
  gap: 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: #ffffff;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
}

.job-form-header h1 {
  color: #020617;
  font-size: 1.5rem;
  font-weight: 650;
  line-height: 2rem;
}

.job-form-header p,
.field-error {
  margin-top: 0.25rem;
  font-size: 0.875rem;
}

.job-form-header p {
  color: #475569;
}

.job-form {
  display: grid;
  gap: 1rem;
}

.field-grid {
  display: grid;
  gap: 1rem;
}

.field {
  display: grid;
  gap: 0.5rem;
}

.field label {
  color: #334155;
  font-size: 0.875rem;
  font-weight: 600;
}

.field input,
.field select,
.field textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 0.375rem;
  color: #020617;
  font-size: 0.875rem;
  outline: none;
  padding: 0.625rem 0.75rem;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
  border-color: #0891b2;
  box-shadow: 0 0 0 3px rgb(8 145 178 / 0.15);
}

.field-error {
  color: #dc2626;
}

.form-alert {
  border: 1px solid #fecaca;
  border-radius: 0.375rem;
  background: #fef2f2;
  color: #b91c1c;
  font-size: 0.875rem;
  padding: 0.625rem 0.75rem;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 0.75rem;
}

.button-primary,
.button-secondary {
  align-items: center;
  border-radius: 0.375rem;
  display: inline-flex;
  font-size: 0.875rem;
  font-weight: 650;
  height: 2.75rem;
  justify-content: center;
  padding: 0 1rem;
}

.button-primary {
  background: #0e7490;
  color: #ffffff;
}

.button-primary:hover {
  background: #155e75;
}

.button-primary:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.button-secondary {
  border: 1px solid #cbd5e1;
  color: #334155;
}

.button-secondary:hover {
  background: #f1f5f9;
}

@media (min-width: 640px) {
  .field-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
