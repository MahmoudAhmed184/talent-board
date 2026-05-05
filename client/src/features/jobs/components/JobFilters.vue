<script setup lang="ts">
import { onMounted } from 'vue'
import { useSearchStore } from '../../../stores/useSearchStore'
import { useTaxonomies } from '../../../composables/useTaxonomies'

const store = useSearchStore()
const { categories, locations, loadTaxonomies } = useTaxonomies()

const emit = defineEmits<{
  search: []
}>()

function handleSearch() {
  emit('search')
}

onMounted(() => {
  loadTaxonomies()
})
</script>

<template>
  <aside class="flex flex-col gap-6">
    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-sm font-semibold text-slate-900">Filters</h2>

      <form class="mt-4 grid gap-4" @submit.prevent="handleSearch">
        <div class="grid gap-1.5">
          <label for="filter-q" class="text-xs font-medium text-slate-700">Keyword</label>
          <input
            id="filter-q"
            v-model="store.q"
            type="search"
            placeholder="Role or keyword"
            class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
        </div>

        <div class="grid gap-1.5">
          <label for="filter-category" class="text-xs font-medium text-slate-700">Category</label>
          <select
            id="filter-category"
            v-model="store.category_id"
            class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option :value="null">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div class="grid gap-1.5">
          <label for="filter-location" class="text-xs font-medium text-slate-700">Location</label>
          <select
            id="filter-location"
            v-model="store.location_id"
            class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option :value="null">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>

        <div class="grid gap-1.5">
          <label for="filter-work-type" class="text-xs font-medium text-slate-700">Work Type</label>
          <select
            id="filter-work-type"
            v-model="store.work_type"
            class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option :value="null">Any Type</option>
            <option value="remote">Remote</option>
            <option value="on-site">On-site</option>
            <option value="hybrid">Hybrid</option>
          </select>
        </div>

        <div class="grid gap-1.5">
          <label for="filter-experience" class="text-xs font-medium text-slate-700">Experience</label>
          <select
            id="filter-experience"
            v-model="store.experience_level"
            class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
          >
            <option :value="null">Any Level</option>
            <option value="junior">Junior</option>
            <option value="mid">Mid</option>
            <option value="senior">Senior</option>
            <option value="lead">Lead</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="grid gap-1.5">
            <label for="filter-salary-min" class="text-xs font-medium text-slate-700">Min Salary</label>
            <input
              id="filter-salary-min"
              v-model.number="store.salary_min"
              type="number"
              min="0"
              class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </div>

          <div class="grid gap-1.5">
            <label for="filter-salary-max" class="text-xs font-medium text-slate-700">Max Salary</label>
            <input
              id="filter-salary-max"
              v-model.number="store.salary_max"
              type="number"
              min="0"
              class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="grid gap-1.5">
            <label for="filter-posted-after" class="text-xs font-medium text-slate-700">Posted After</label>
            <input
              id="filter-posted-after"
              v-model="store.posted_after"
              type="date"
              class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </div>

          <div class="grid gap-1.5">
            <label for="filter-posted-before" class="text-xs font-medium text-slate-700">Posted Before</label>
            <input
              id="filter-posted-before"
              v-model="store.posted_before"
              type="date"
              class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
            >
          </div>
        </div>

        <button
          type="submit"
          class="mt-2 inline-flex h-10 items-center justify-center rounded-md bg-emerald-700 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800"
        >
          Apply Filters
        </button>

        <button
          type="button"
          class="text-xs font-medium text-slate-500 hover:text-slate-700 disabled:opacity-50"
          :disabled="store.activeFiltersCount === 0"
          @click="store.resetFilters(); handleSearch();"
        >
          Reset all
        </button>
      </form>
    </div>
  </aside>
</template>
