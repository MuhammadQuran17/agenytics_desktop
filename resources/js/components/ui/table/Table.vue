<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';

interface Props<T> {
  data: T | T[];
  class?: HTMLAttributes['class'];
  rowClass?: HTMLAttributes['class'];
  headerClass?: HTMLAttributes['class'];
  cellClass?: HTMLAttributes['class'];
  emptyMessage?: string;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props<Record<string, any>>>(), {
  emptyMessage: 'No data available',
  isLoading: false
});

const normalizedData = computed(() => {
  // If data is an array, use it directly
  if (Array.isArray(props.data)) {
    return props.data;
  }
  // If data is a single object, wrap it in an array
  else if (props.data && typeof props.data === 'object') {
    return [props.data];
  }
  // Otherwise, return an empty array
  return [];
});

const columns = computed(() => {
  if (normalizedData.value.length === 0) return [];
  return Object.keys(normalizedData.value[0]);
});

const hasData = computed(() => normalizedData.value.length > 0);
</script>

<template>
  <div class="w-full overflow-x-auto rounded-lg border border-border">
    <table class="w-full text-sm" :class="props.class">
      <thead v-if="hasData" class="bg-muted">
        <tr>
          <th 
            v-for="column in columns" 
            :key="column"
            class="px-4 py-3 text-left font-medium text-primary"
            :class="props.headerClass"
          >
            {{ column }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border">
        <tr 
          v-for="(row, i) in normalizedData" 
          :key="i" 
          class="bg-card hover:bg-muted/50"
          :class="props.rowClass"
        >
          <td 
            v-for="(value, column) in row" 
            :key="column"
            class="px-4 py-2 text-card-foreground"
            :class="props.cellClass"
          >
            {{ value === null ? 'NULL' : value }}
          </td>
        </tr>
        <tr v-if="!hasData && !isLoading">
          <td 
            class="px-4 py-6 text-center text-muted-foreground" 
            :colspan="columns.length || 1"
          >
            {{ emptyMessage }}
          </td>
        </tr>
        <tr v-if="isLoading">
          <td 
            class="px-4 py-6 text-center text-muted-foreground" 
            :colspan="columns.length || 1"
          >
            <div class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Loading data...</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>