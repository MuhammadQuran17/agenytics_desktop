<script setup lang="ts">
import { ref, computed } from 'vue';
import Table from './Table.vue';

interface Column {
    key: string;
    label: string;
}

interface TableData {
    table_id: string;
    title: string;
    columns: Column[];
    rows: Record<string, any>[];
}

interface Props {
    tables: TableData[];
}

const props = defineProps<Props>();

const activeTableId = ref<string>(props.tables?.[0]?.table_id || '');

const activeTable = computed(() => {
    return props.tables?.find(t => t.table_id === activeTableId.value) || props.tables?.[0];
});

const hasTables = computed(() => props.tables && props.tables.length > 0);
</script>

<template>
    <div v-if="hasTables" class="dynamic-tables-container">
        <!-- Tabs Navigation -->
        <div class="tabs-nav flex gap-2 mb-4 overflow-x-auto pb-2">
            <button
                v-for="table in tables"
                :key="table.table_id"
                @click="activeTableId = table.table_id"
                class="tab-button px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap"
                :class="[
                    activeTableId === table.table_id
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : 'bg-muted text-muted-foreground hover:bg-muted/80'
                ]"
            >
                {{ table.title }}
            </button>
        </div>

        <!-- Active Table Content -->
        <div v-if="activeTable" class="table-content">
            <Table
                :columns="activeTable.columns"
                :rows="activeTable.rows"
            />
        </div>

        <!-- No Tables Message -->
        <div v-else class="p-8 text-center text-muted-foreground">
            <p>No table data available</p>
        </div>
    </div>

    <!-- Empty State -->
    <div v-else class="p-8 text-center text-muted-foreground">
        <p>No tables to display</p>
    </div>
</template>

<style scoped>
.dynamic-tables-container {
    width: 100%;
}

.tabs-nav {
    border-bottom: 1px solid var(--border);
}

.tabs-nav::-webkit-scrollbar {
    height: 6px;
}

.tabs-nav::-webkit-scrollbar-track {
    background: transparent;
}

.tabs-nav::-webkit-scrollbar-thumb {
    background-color: var(--muted);
    border-radius: 3px;
}

.tab-button {
    cursor: pointer;
}

.tab-button:focus-visible {
    outline: 2px solid var(--ring);
    outline-offset: 2px;
}

.table-content {
    animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
