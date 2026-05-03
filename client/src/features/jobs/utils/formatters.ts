/**
 * Shared formatting utilities for job listing display.
 *
 * Extracted from JobCard.vue and JobDetail.vue to avoid duplication.
 */

const currencyFormatter = new Intl.NumberFormat('en-US', {
  maximumFractionDigits: 0,
  style: 'currency',
  currency: 'USD',
})

const dateFormatter = new Intl.DateTimeFormat('en-US', {
  month: 'short',
  day: 'numeric',
  year: 'numeric',
})

/**
 * Convert snake_case or kebab-case strings to Sentence Case.
 * Returns "Not specified" for nullish/empty values.
 */
export function sentenceCase(value?: string | null): string {
  if (!value) return 'Not specified'
  return value
    .replaceAll('_', ' ')
    .replaceAll('-', ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

/**
 * Build a human-readable salary range label.
 */
export function formatSalaryRange(
  min?: number | null,
  max?: number | null,
): string {
  if (min && max) return `${currencyFormatter.format(min)} – ${currencyFormatter.format(max)}`
  if (min) return `From ${currencyFormatter.format(min)}`
  if (max) return `Up to ${currencyFormatter.format(max)}`
  return 'Salary not listed'
}

/**
 * Truncate a description to a card-friendly snippet.
 */
export function descriptionSnippet(
  text?: string | null,
  maxLength = 260,
): string {
  const cleaned = text?.replace(/\s+/g, ' ').trim()
  if (!cleaned) return 'Open role with details available on the listing page.'
  return cleaned.length > maxLength
    ? `${cleaned.slice(0, maxLength)}…`
    : cleaned
}

/**
 * Format an ISO date string to a short human-readable label.
 */
export function formatPostedDate(value?: string | null): string {
  if (!value) return 'Posted recently'
  return dateFormatter.format(new Date(value))
}
