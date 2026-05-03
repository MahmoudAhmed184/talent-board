import { ref } from 'vue'
import { http } from '../http'

export interface TaxonomyItem {
  id: number
  name: string
  slug: string
}

export function useTaxonomies() {
  const categories = ref<TaxonomyItem[]>([])
  const locations = ref<TaxonomyItem[]>([])
  const isLoading = ref(false)

  async function loadTaxonomies() {
    isLoading.value = true
    try {
      const [catRes, locRes] = await Promise.all([
        http.get<{ data: TaxonomyItem[] }>('/api/v1/categories'),
        http.get<{ data: TaxonomyItem[] }>('/api/v1/locations'),
      ])
      categories.value = catRes.data.data
      locations.value = locRes.data.data
    } catch (error) {
      console.error('Failed to load taxonomies', error)
    } finally {
      isLoading.value = false
    }
  }

  return {
    categories,
    locations,
    isLoading,
    loadTaxonomies,
  }
}
