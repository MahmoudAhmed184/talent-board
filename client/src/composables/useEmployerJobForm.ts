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

const safeTrim = (value: any): string => {
  if (value === null || value === undefined) {
    return '';
  }
  return String(value).trim();
};

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

const toNullableNumber = (value: any): number | null => {
  if (value === null || value === undefined) {
    return null;
  }

  const strValue = String(value).trim();
  if (strValue === '') {
    return null;
  }

  const parsed = Number(strValue);
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

    if (!safeTrim(values.title)) {
      setFieldError('title', 'Job title is required.');
    }
    if (!safeTrim(values.description)) {
      setFieldError('description', 'Description is required.');
    }
    if (!safeTrim(values.location)) {
      setFieldError('location', 'Location is required.');
    }
    if (!safeTrim(values.category)) {
      setFieldError('category', 'Category is required.');
    }
    if (!safeTrim(values.workType)) {
      setFieldError('workType', 'Work type is required.');
    }
    if (!safeTrim(values.experienceLevel)) {
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
    title: safeTrim(values.title),
    description: safeTrim(values.description),
    responsibilities: safeTrim(values.responsibilities) || null,
    qualifications: safeTrim(values.qualifications) || null,
    location: safeTrim(values.location),
    category: safeTrim(values.category),
    work_type: safeTrim(values.workType),
    experience_level: safeTrim(values.experienceLevel),
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
