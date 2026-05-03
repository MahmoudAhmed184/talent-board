import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useSearchStore = defineStore('search', () => {
  const q = ref('')
  const category_id = ref<number | null>(null)
  const location_id = ref<number | null>(null)
  const work_type = ref<string | null>(null)
  const experience_level = ref<string | null>(null)
  const salary_min = ref<number | null>(null)
  const salary_max = ref<number | null>(null)
  const posted_after = ref<string | null>(null)
  const page = ref(1)

  const activeFiltersCount = computed(() => {
    let count = 0
    if (q.value) count++
    if (category_id.value !== null) count++
    if (location_id.value !== null) count++
    if (work_type.value) count++
    if (experience_level.value) count++
    if (salary_min.value !== null) count++
    if (salary_max.value !== null) count++
    if (posted_after.value) count++
    return count
  })

  function resetFilters() {
    q.value = ''
    category_id.value = null
    location_id.value = null
    work_type.value = null
    experience_level.value = null
    salary_min.value = null
    salary_max.value = null
    posted_after.value = null
    page.value = 1
  }

  function setPage(p: number) {
    page.value = p
  }

  return {
    q,
    category_id,
    location_id,
    work_type,
    experience_level,
    salary_min,
    salary_max,
    posted_after,
    page,
    activeFiltersCount,
    resetFilters,
    setPage,
  }
})
