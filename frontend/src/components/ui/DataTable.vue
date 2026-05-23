<template>
  <div class="bg-white rounded-lg shadow border border-slate-200 overflow-hidden flex flex-col">
    <div class="overflow-x-auto relative flex-1">
      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center z-10">
        <div class="text-indigo-600 font-medium">Loading...</div>
      </div>
      
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th 
              v-for="col in columns" 
              :key="col.key"
              class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider select-none"
              :class="[col.align === 'right' ? 'text-right' : 'text-left', col.sortable ? 'cursor-pointer hover:text-slate-900' : '']"
              @click="col.sortable ? handleSort(col.key) : null"
            >
              <div class="flex items-center gap-1" :class="col.align === 'right' ? 'justify-end' : ''">
                {{ col.label }}
                <span v-if="col.sortable && sortBy === col.key" class="text-indigo-600">
                  {{ sortDir === 'asc' ? '↑' : '↓' }}
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr 
            v-for="(row, index) in data" 
            :key="row.id || index" 
            class="hover:bg-slate-50 transition-colors"
            :class="rowClass ? rowClass(row) : ''"
          >
            <td 
              v-for="col in columns" 
              :key="col.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-slate-800"
              :class="col.align === 'right' ? 'text-right' : 'text-left'"
            >
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                <span class="text-slate-700">{{ row[col.key] }}</span>
              </slot>
            </td>
          </tr>
          
          <tr v-if="!loading && data.length === 0">
            <td :colspan="columns.length" class="px-6 py-8 text-center text-slate-500">
              {{ emptyMessage }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination Footer -->
    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-3 bg-slate-50 flex items-center justify-between border-t border-slate-200">
      <div class="text-xs text-slate-500 hidden sm:block">
        Showing page <span class="font-semibold text-slate-800">{{ pagination.current_page }}</span> of <span class="font-semibold text-slate-800">{{ pagination.last_page }}</span> 
        (<span class="font-semibold text-slate-800">{{ pagination.total }}</span> total records)
      </div>
      <div class="flex space-x-2">
        <button 
          @click="$emit('page-change', pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-3 py-1 bg-white text-slate-700 border border-slate-300 rounded hover:bg-slate-50 disabled:opacity-50 text-sm transition-colors font-medium shadow-sm"
        >
          Previous
        </button>
        <button 
          @click="$emit('page-change', pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-3 py-1 bg-white text-slate-700 border border-slate-300 rounded hover:bg-slate-50 disabled:opacity-50 text-sm transition-colors font-medium shadow-sm"
        >
          Next
        </button>
      </div>
    </div>
  </div></template>

<script setup>
const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  pagination: { type: Object, default: null },
  sortBy: { type: String, default: '' },
  sortDir: { type: String, default: 'desc' },
  emptyMessage: { type: String, default: 'No records found.' },
  rowClass: { type: Function, default: null }
});

const emit = defineEmits(['sort', 'page-change']);

const handleSort = (key) => {
  emit('sort', key);
};
</script>
