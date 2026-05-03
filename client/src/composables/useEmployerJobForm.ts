import { reactive, ref } from 'vue';
import { useFormErrors, type ApiErrorPayload } from './useFormErrors';

export const EMPLOYER_JOB_FIELDS = [
  'title',
  'description',
  'responsibilities',
  'qualifications',
  'location',
  'category',
  'workType',
  'experienceLevel',
  'salaryMin',
  'salaryMax',
  'expiresAt',
] as const;

export type EmployerJobField = (typeof EMPLOYER_JOB_FIELDS)[number];

export interface EmployerJobFormData {
  title: string;
  description: string;
  responsibilities: string;
  qualifications: string;
  location: string;
  category: string;
  workType: string;
  experienceLevel: string;
  salaryMin: string;
  salaryMax: string;
  expiresAt: string;
}

export interface EmployerJobPayload {
  title: string;
  description: string;
  responsibilities: string | null;
  qualifications: string | null;
  location: string;
  category: string;
  work_type: string;
  experience_level: string;
  salary_min: number | null;
  salary_max: number | null;
  expires_at: string | null;
}

interface UseEmployerJobFormOptions {
  initialValues?: Partial<EmployerJobFormData>;
  onSubmit?: (payload: EmployerJobPayload) => Promise<void>;
}

const defaultValues = (): EmployerJobFormData => ({
  title: '',
  description: '',
  responsibilities: '',
  qualifications: '',
  location: '',
  category: '',
  workType: '',
  experienceLevel: '',
  salaryMin: '',
  salaryMax: '',
  expiresAt: '',
});

const toNullableNumber = (value: string): number | null => {
  if (value.trim() === '') {
    return null;
  }

  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : null;
};

export function useEmployerJobForm(options: UseEmployerJobFormOptions = {}) {
  const values = reactive<EmployerJobFormData>({
    ...defaultValues(),
    ...options.initialValues,
  });
  const isSubmitting = ref(false);
  const { state, clearErrors, setFieldError, getFieldError, mapApiErrors } =
    useFormErrors<EmployerJobField>();

  const validate = (): boolean => {
    clearErrors();

    if (!values.title.trim()) {
      setFieldError('title', 'Job title is required.');
    }
    if (!values.description.trim()) {
      setFieldError('description', 'Description is required.');
    }
    if (!values.location.trim()) {
      setFieldError('location', 'Location is required.');
    }
    if (!values.category.trim()) {
      setFieldError('category', 'Category is required.');
    }
    if (!values.workType.trim()) {
      setFieldError('workType', 'Work type is required.');
    }
    if (!values.experienceLevel.trim()) {
      setFieldError('experienceLevel', 'Experience level is required.');
    }

    const salaryMin = toNullableNumber(values.salaryMin);
    const salaryMax = toNullableNumber(values.salaryMax);
    if (salaryMin !== null && salaryMax !== null && salaryMin > salaryMax) {
      setFieldError('salaryMax', 'Maximum salary must be >= minimum salary.');
    }

    return Object.keys(state.fieldErrors.value).length === 0;
  };

  const normalizedPayload = (): EmployerJobPayload => ({
    title: values.title.trim(),
    description: values.description.trim(),
    responsibilities: values.responsibilities.trim() || null,
    qualifications: values.qualifications.trim() || null,
    location: values.location.trim(),
    category: values.category.trim(),
    work_type: values.workType.trim(),
    experience_level: values.experienceLevel.trim(),
    salary_min: toNullableNumber(values.salaryMin),
    salary_max: toNullableNumber(values.salaryMax),
    expires_at: values.expiresAt || null,
  });

  const submit = async (): Promise<boolean> => {
    if (!validate()) {
      return false;
    }

    if (!options.onSubmit) {
      return true;
    }

    isSubmitting.value = true;
    try {
      await options.onSubmit(normalizedPayload());
      return true;
    } catch (error) {
      mapApiErrors(error as ApiErrorPayload, {
        work_type: 'workType',
        experience_level: 'experienceLevel',
        salary_min: 'salaryMin',
        salary_max: 'salaryMax',
        expires_at: 'expiresAt',
      });
      return false;
    } finally {
      isSubmitting.value = false;
    }
  };

  const reset = (): void => {
    Object.assign(values, defaultValues(), options.initialValues);
    clearErrors();
  };

  const setValues = (nextValues: Partial<EmployerJobFormData>): void => {
    Object.assign(values, defaultValues(), nextValues);
    clearErrors();
  };

  const applyApiErrors = (
    error: ApiErrorPayload,
  ): void => {
    mapApiErrors(error, {
      work_type: 'workType',
      experience_level: 'experienceLevel',
      salary_min: 'salaryMin',
      salary_max: 'salaryMax',
      expires_at: 'expiresAt',
    });
  };
  const getFormError = (): string | null => state.formError.value;

  return {
    values,
    isSubmitting,
    validate,
    normalizedPayload,
    submit,
    reset,
    setValues,
    applyApiErrors,
    getFormError,
    getFieldError,
  };
}
