import axios, { AxiosError } from 'axios'
import type { AxiosResponse } from 'axios'

declare module 'axios' {
  export interface AxiosRequestConfig {
    skipUnauthorizedHandler?: boolean
  }
}

export type ValidationErrorMap = Record<string, string[]>

export interface ValidationErrorResponse {
  message: string
  errors: ValidationErrorMap
}

interface LaravelValidationResponse {
  message?: unknown
  errors?: unknown
}

type UnauthorizedHandler = () => Promise<void> | void

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000'

let unauthorizedHandler: UnauthorizedHandler | undefined
let unauthorizedHandlerRunning = false

export class ApiValidationError extends Error {
  readonly status = 422
  readonly errors: ValidationErrorMap
  readonly response?: AxiosResponse<LaravelValidationResponse>

  constructor(message: string, errors: ValidationErrorMap, response?: AxiosResponse<LaravelValidationResponse>) {
    super(message)
    this.name = 'ApiValidationError'
    this.errors = errors
    this.response = response
  }
}

export const http = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true,
})

export function setUnauthorizedHandler(handler: UnauthorizedHandler | undefined) {
  unauthorizedHandler = handler
}

export function isApiValidationError(error: unknown): error is ApiValidationError {
  return error instanceof ApiValidationError
}

function normalizeValidationErrors(value: unknown): ValidationErrorMap {
  if (!value || typeof value !== 'object' || Array.isArray(value)) {
    return {}
  }

  return Object.fromEntries(
    Object.entries(value).map(([field, messages]) => {
      if (Array.isArray(messages)) {
        return [field, messages.map((message) => String(message))]
      }

      return [field, [String(messages)]]
    }),
  )
}

function getValidationMessage(data: LaravelValidationResponse | undefined): string {
  return typeof data?.message === 'string' && data.message.length > 0
    ? data.message
    : 'The given data was invalid.'
}

async function handleUnauthorized(error: AxiosError) {
  if (error.config?.skipUnauthorizedHandler || !unauthorizedHandler || unauthorizedHandlerRunning) {
    return
  }

  unauthorizedHandlerRunning = true

  try {
    await unauthorizedHandler()
  } finally {
    unauthorizedHandlerRunning = false
  }
}

http.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<LaravelValidationResponse>) => {
    const status = error.response?.status

    if (status === 401) {
      await handleUnauthorized(error)
      return Promise.reject(error)
    }

    if (status === 422) {
      const validationError = new ApiValidationError(
        getValidationMessage(error.response?.data),
        normalizeValidationErrors(error.response?.data?.errors),
        error.response,
      )

      return Promise.reject(validationError)
    }

    return Promise.reject(error)
  },
)
