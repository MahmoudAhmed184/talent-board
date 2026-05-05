<script setup lang="ts">
import { onMounted } from 'vue'
import { useSearchStore } from '../stores/useSearchStore'
import { useTaxonomies } from '../composables/useTaxonomies'
import { Filter, RotateCcw, Search, MapPin, Briefcase, GraduationCap } from 'lucide-vue-next'

interface Props {
  layout?: 'sidebar' | 'topbar'
}

const props = withDefaults(defineProps<Props>(), {
  layout: 'sidebar',
})

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
  <!-- Sidebar Layout -->
  <aside v-if="layout === 'sidebar'" class="flex flex-col gap-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
        <Filter class="w-5 h-5 text-emerald-600" />
        <h2 class="text-base font-bold text-slate-900">Filters</h2>
      </div>
      
      <form class="grid gap-5" @submit.prevent="handleSearch">
        <!-- Keyword -->
        <div class="grid gap-1.5">
          <label for="filter-q" class="text-sm font-medium text-slate-700">Keyword</label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              id="filter-q"
              v-model="store.q"
              type="search"
              placeholder="Role or keyword"
              class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-sm transition-all placeholder:text-slate-400"
            >
          </div>
        </div>

        <!-- Category -->
        <div class="grid gap-1.5">
          <label for="filter-category" class="text-sm font-medium text-slate-700">Category</label>
          <div class="relative">
            <GraduationCap class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            <select
              id="filter-category"
              v-model="store.category_id"
              class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-sm transition-all appearance-none"
            >
              <option :value="null">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Location -->
        <div class="grid gap-1.5">
          <label for="filter-location" class="text-sm font-medium text-slate-700">Location</label>
          <div class="relative">
            <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            <select
              id="filter-location"
              v-model="store.location_id"
              class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-sm transition-all appearance-none"
            >
              <option :value="null">All Locations</option>
              <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                {{ loc.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Work Type -->
        <div class="grid gap-1.5">
          <label for="filter-work-type" class="text-sm font-medium text-slate-700">Work Type</label>
          <div class="relative">
            <Briefcase class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            <select
              id="filter-work-type"
              v-model="store.work_type"
              class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-sm transition-all appearance-none"
            >
              <option :value="null">Any Type</option>
              <option value="remote">Remote</option>
              <option value="on-site">On-site</option>
              <option value="hybrid">Hybrid</option>
            </select>
          </div>
        </div>

        <!-- Experience Level -->
        <div class="grid gap-1.5">
          <label for="filter-experience" class="text-sm font-medium text-slate-700">Experience</label>
          <select
            id="filter-experience"
            v-model="store.experience_level"
            class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-sm transition-all appearance-none"
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
            v-if="store.activeFiltersCount > 0"
            type="button"
            class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-slate-500 hover:text-red-600 transition-colors"
            @click="store.resetFilters(); handleSearch();"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            Reset all
          </button>
        </div>
      </form>
    </div>
  </aside>

  <!-- Topbar Layout -->
  <div v-else class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm mb-6">
    <form class="flex flex-wrap items-center gap-3" @submit.prevent="handleSearch">
      <!-- Keyword -->
      <div class="relative flex-1 min-w-[200px]">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input
          v-model="store.q"
          type="search"
          placeholder="Keyword or role..."
          class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all"
        >
      </div>

      <!-- Category -->
      <div class="relative min-w-[160px]">
        <select
          v-model="store.category_id"
          class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-3 pr-8 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer"
        >
          <option :value="null">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
      </div>

      <!-- Location -->
      <div class="relative min-w-[160px]">
        <select
          v-model="store.location_id"
          class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-3 pr-8 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer"
        >
          <option :value="null">All Locations</option>
          <option v-for="loc in locations" :key="loc.id" :value="loc.id">
            {{ loc.name }}
          </option>
        </select>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
      </div>

      <!-- Work Type -->
      <div class="relative min-w-[140px]">
        <select
          v-model="store.work_type"
          class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-3 pr-8 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer"
        >
          <option :value="null">Work Type</option>
          <option value="remote">Remote</option>
          <option value="on-site">On-site</option>
          <option value="hybrid">Hybrid</option>
        </select>
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
      </div>

      <div class="flex items-center gap-2 ml-auto">
        <button
          v-if="store.activeFiltersCount > 0"
          type="button"
          class="h-10 px-3 inline-flex items-center justify-center gap-1.5 text-sm font-medium text-slate-500 hover:text-red-600 transition-colors border border-transparent rounded-lg hover:bg-red-50"
          @click="store.resetFilters(); handleSearch();"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          Reset
        </button>

        <button
          type="submit"
          class="h-10 px-5 inline-flex items-center justify-center rounded-lg bg-emerald-600 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-all active:scale-95"
        >
          Search
        </button>
      </div>
    </form>
  </div>
</template>
