import { ref } from 'vue';

export type FormFieldErrors<TFields extends string = string> = Partial<
  Record<TFields, string[]>
>;

export interface ApiErrorPayload {
  message?: string;
  errors?: Record<string, string[]>;
  status?: number;
  response?: {
    status?: number;
    data?: {
      message?: string;
      errors?: Record<string, string[]>;
    };
  };
}

export interface FormErrorState<TFields extends string = string> {
  fieldErrors: FormFieldErrors<TFields>;
  formError: string | null;
}

export function useFormErrors<TFields extends string = string>() {
  const fieldErrors = ref<FormFieldErrors<TFields>>({});
  const formError = ref<string | null>(null);

  const clearErrors = (): void => {
    fieldErrors.value = {};
    formError.value = null;
  };

  const setFieldError = (field: TFields, message: string): void => {
    fieldErrors.value = {
      ...fieldErrors.value,
      [field]: [message],
    };
  };

  const setFormError = (message: string): void => {
    formError.value = message;
  };

  const getFieldError = (field: TFields): string | null => {
    const messages = fieldErrors.value[field];
    return messages && messages.length > 0 ? messages[0] : null;
  };

  const mapApiErrors = (error: ApiErrorPayload): void => {
    clearErrors();

    const status = error.response?.status ?? error.status;
    const dataMessage = error.response?.data?.message ?? error.message;
    const dataErrors = error.response?.data?.errors ?? error.errors;

    if (status === 422 && dataErrors) {
      const mapped: FormFieldErrors<TFields> = {};
      for (const [key, messages] of Object.entries(dataErrors)) {
        mapped[key as TFields] = messages;
      }

      fieldErrors.value = mapped;
      formError.value = dataMessage ?? 'Please review the highlighted fields.';
      return;
    }

    formError.value = dataMessage ?? 'Something went wrong. Please try again.';
  };

  return {
    state: {
      fieldErrors,
      formError,
    },
    clearErrors,
    setFieldError,
    setFormError,
    getFieldError,
    mapApiErrors,
  };
}
