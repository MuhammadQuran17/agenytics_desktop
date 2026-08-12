<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';

interface Column {
  key: string;
  label: string;
}

interface TableData {
  columns?: Column[];
  rows?: Record<string, any>[];
}

interface Props {
  data?: TableData | Record<string, any>[] | any;
  columns?: Column[];
  rows?: Record<string, any>[];
  class?: HTMLAttributes['class'];
  rowClass?: HTMLAttributes['class'];
  headerClass?: HTMLAttributes['class'];
  cellClass?: HTMLAttributes['class'];
  emptyMessage?: string;
  isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  emptyMessage: 'No data available',
  isLoading: false
});

// Normalize data from different formats
const normalizedData = computed(() => {
  // Priority 1: Direct rows prop
  if (props.rows && Array.isArray(props.rows)) {
    return props.rows;
  }

  // Priority 2: Data object with rows property (N8N format)
  if (props.data && typeof props.data === 'object' && !Array.isArray(props.data)) {
    if (props.data.rows && Array.isArray(props.data.rows)) {
      return props.data.rows;
    }
  }

  // Priority 3: Data is an array directly
  if (Array.isArray(props.data)) {
    return props.data;
  }

  // Priority 4: Data is a single object, wrap it
  if (props.data && typeof props.data === 'object') {
    return [props.data];
  }

  return [];
});

const normalizedColumns = computed<Column[]>(() => {
  // Priority 1: Direct columns prop
  if (props.columns && Array.isArray(props.columns)) {
    return props.columns;
  }

  // Priority 2: Data object with columns property (N8N format)
  if (props.data && typeof props.data === 'object' && !Array.isArray(props.data)) {
    if (props.data.columns && Array.isArray(props.data.columns)) {
      return props.data.columns;
    }
  }

  // Priority 3: Auto-generate from first row
  if (normalizedData.value.length > 0) {
    return Object.keys(normalizedData.value[0]).map(key => ({
      key,
      label: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')
    }));
  }

  return [];
});

const hasData = computed(() => normalizedData.value.length > 0);
</script>

<template>
  <div class="w-full overflow-x-auto rounded-lg border border-border">
    <table class="w-full text-sm" :class="props.class">
      <thead v-if="hasData" class="bg-muted">
        <tr>
          <th 
            v-for="column in normalizedColumns" 
            :key="column.key"
            class="px-4 py-3 text-left font-medium text-primary"
            :class="props.headerClass"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border">
        <tr 
          v-for="(row, i) in normalizedData" 
          :key="i" 
          class="bg-card hover:bg-muted/50 transition-colors"
          :class="props.rowClass"
        >
          <td 
            v-for="column in normalizedColumns" 
            :key="column.key"
            class="px-4 py-2 text-card-foreground"
            :class="props.cellClass"
          >
            {{ row[column.key] === null || row[column.key] === undefined ? '-' : row[column.key] }}
          </td>
        </tr>
        <tr v-if="!hasData && !isLoading">
          <td 
            class="px-4 py-6 text-center text-muted-foreground" 
            :colspan="normalizedColumns.length || 1"
          >
            {{ emptyMessage }}
          </td>
        </tr>
        <tr v-if="isLoading">
          <td 
            class="px-4 py-6 text-center text-muted-foreground" 
            :colspan="normalizedColumns.length || 1"
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