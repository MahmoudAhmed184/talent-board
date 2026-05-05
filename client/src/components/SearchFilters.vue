<script setup lang="ts">
import { onMounted } from 'vue'
import { useSearchStore } from '../stores/useSearchStore'
import { useTaxonomies } from '../composables/useTaxonomies'
import { Filter, RotateCcw } from 'lucide-vue-next'

const store = useSearchStore()
const { categories, locations, loadTaxonomies } = useTaxonomies()

const emit = defineEmits(['search'])

function handleSearch() {
  emit('search')
}

onMounted(() => {
  loadTaxonomies()
})
</script>

<template>
  <aside class="flex flex-col gap-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
        <Filter class="w-5 h-5 text-emerald-600" />
        <h2 class="text-base font-bold text-slate-900">Filters</h2>
      </div>
      
      <form class="grid gap-5" @submit.prevent="handleSearch">
        <!-- Keyword -->
        <div class="grid gap-1.5">
          <label for="filter-q" class="text-sm font-medium text-slate-700">Keyword</label>
          <input
            id="filter-q"
            v-model="store.q"
            type="search"
            placeholder="Role or keyword"
            class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all"
          >
        </div>

        <!-- Category -->
        <div class="grid gap-1.5">
          <label for="filter-category" class="text-sm font-medium text-slate-700">Category</label>
          <select
            id="filter-category"
            v-model="store.category_id"
            class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all"
          >
            <option :value="null">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Location -->
        <div class="grid gap-1.5">
          <label for="filter-location" class="text-sm font-medium text-slate-700">Location</label>
          <select
            id="filter-location"
            v-model="store.location_id"
            class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all"
          >
            <option :value="null">All Locations</option>
            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
              {{ loc.name }}
            </option>
          </select>
        </div>

        <!-- Work Type -->
        <div class="grid gap-1.5">
          <label for="filter-work-type" class="text-sm font-medium text-slate-700">Work Type</label>
          <select
            id="filter-work-type"
            v-model="store.work_type"
            class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all"
          >
            <option :value="null">Any Type</option>
            <option value="remote">Remote</option>
            <option value="on-site">On-site</option>
            <option value="hybrid">Hybrid</option>
          </select>
        </div>

        <!-- Experience Level -->
        <div class="grid gap-1.5">
          <label for="filter-experience" class="text-sm font-medium text-slate-700">Experience</label>
          <select
            id="filter-experience"
            v-model="store.experience_level"
            class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all"
          >
            <option :value="null">Any Level</option>
            <option value="junior">Junior</option>
            <option value="mid">Mid</option>
            <option value="senior">Senior</option>
            <option value="lead">Lead</option>
          </select>
        </div>

        <div class="mt-4 flex flex-col gap-3">
          <button
            type="submit"
            class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all active:scale-95"
          >
            Apply Filters
          </button>

          <button
            type="button"
            class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700 disabled:opacity-50 transition-colors"
            :disabled="store.activeFiltersCount === 0"
            @click="store.resetFilters(); handleSearch();"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            Reset all
          </button>
        </div>
      </form>
    </div>
  </aside>
</template>
