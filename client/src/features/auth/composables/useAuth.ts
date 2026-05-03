import type { Router, RouteLocationNormalized } from 'vue-router'
import { http, setUnauthorizedHandler } from '../../../http'
import { useAuthStore } from '../stores/useAuthStore'
import type { AuthResourceResponse, LoginPayload, RegisterPayload, UserRole } from '../types'

function roleHome(role: UserRole | null) {
  if (role === 'candidate') {
    return '/candidate/applications'
  }

  if (role === 'employer') {
    return '/employer/dashboard'
  }

  if (role === 'admin') {
    return '/admin'
  }

  return '/'
}

function getRedirectTarget(to: RouteLocationNormalized) {
  return typeof to.query.redirect === 'string' && to.query.redirect.startsWith('/')
    ? to.query.redirect
    : null
}

export function useAuth() {
  const authStore = useAuthStore()

  async function prepareSpaSession() {
    await http.get('/sanctum/csrf-cookie', {
      skipUnauthorizedHandler: true,
    })
  }

  async function restoreSession() {
    authStore.setSessionLoading(true)

    try {
      const response = await http.get<AuthResourceResponse>('/api/v1/auth/me', {
        skipUnauthorizedHandler: true,
      })

      authStore.setSession(response.data.data)

      return response.data.data
    } catch {
      authStore.clearSession()
      return null
    } finally {
      authStore.setSessionLoading(false)
    }
  }

  async function login(payload: LoginPayload) {
    await prepareSpaSession()

    const response = await http.post<AuthResourceResponse>('/api/v1/auth/login', payload, {
      skipUnauthorizedHandler: true,
    })

    authStore.setSession(response.data.data)

    return response.data.data
  }

  async function register(payload: RegisterPayload) {
    await prepareSpaSession()

    const response = await http.post<AuthResourceResponse>('/api/v1/auth/register', payload, {
      skipUnauthorizedHandler: true,
    })

    authStore.setSession(response.data.data)

    return response.data.data
  }

  async function logout() {
    try {
      await http.post('/api/v1/auth/logout', undefined, {
        skipUnauthorizedHandler: true,
      })
    } finally {
      logoutLocally()
    }
  }

  function logoutLocally() {
    authStore.clearSession()
    authStore.setSessionLoading(false)
  }

  return {
    login,
    logout,
    logoutLocally,
    register,
    restoreSession,
  }
}

export function installAuthSession(router: Router) {
  const auth = useAuth()
  const authStore = useAuthStore()
  const initialSession = auth.restoreSession()

  setUnauthorizedHandler(() => {
    auth.logoutLocally()
  })

  router.beforeEach(async (to) => {
    await initialSession

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return {
        path: '/auth/login',
        query: { redirect: to.fullPath },
      }
    }

    if (to.meta.role && authStore.role !== to.meta.role) {
      return roleHome(authStore.role)
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
      return getRedirectTarget(to) ?? roleHome(authStore.role)
    }

    return true
  })
}
