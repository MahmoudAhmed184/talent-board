import 'vue-router'

export type RouteRole = 'candidate' | 'employer' | 'admin'

declare module 'vue-router' {
  interface RouteMeta {
    guestOnly?: boolean
    layout?: 'app' | 'standalone'
    requiresAuth?: boolean
    role?: RouteRole
  }
}
