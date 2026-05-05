import type { PublicJobDetail } from '../../composables/usePublicJobs'

export interface Resume {
  id: number
  original_name: string
  mime_type: string | null
  size: number | null
  created_at: string
}

export interface CandidateProfile {
  id: number
  summary: string | null
  location_text: string | null
  phone: string | null
  skills: string[]
  default_resume_id: number | null
  default_resume?: Resume | null
  updated_at: string
}

export interface CandidateApplication {
  id: number
  job_listing_id: number
  job_listing?: Pick<PublicJobDetail, 'id' | 'title' | 'employer'>
  status: string
  submission_mode: 'resume' | 'contact'
  cover_letter: string | null
  contact_email: string | null
  contact_phone: string | null
  resume?: Pick<Resume, 'id' | 'original_name'> | null
  submitted_at: string
  decision?: {
    note: string | null
    decided_at: string | null
  }
}

export interface UpdateCandidateProfilePayload {
  summary?: string | null
  location_text?: string | null
  phone?: string | null
  skills?: string[]
  default_resume_id?: number | null
}
