import 'vue-router'

export type RouteRole = 'candidate' | 'employer' | 'admin'

declare module 'vue-router' {
  interface RouteMeta {
    guestOnly?: boolean
    requiresAuth?: boolean
    role?: RouteRole
  }
}
