export interface JsonApiPaginationLinks {
  first?: string | null
  last?: string | null
  next?: string | null
  prev?: string | null
}

export interface JsonApiPaginationMetaLink {
  active: boolean
  label: string
  url: string | null
}

export interface JsonApiPaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  links?: JsonApiPaginationMetaLink[]
  path?: string
  per_page: number
  to: number | null
  total: number
}

export interface JsonApiPaginatedResponse<T> {
  data: T[]
  links: JsonApiPaginationLinks
  meta: JsonApiPaginationMeta
}
