<script setup lang="ts">
import { computed, onMounted, type Component } from 'vue';
import { useUiComponentLoader } from '@/composables/useUiComponentLoader';

interface UiBlock {
    ui_type: string;
    title: string;
    description: string;
    data: any;
}

const props = defineProps<{
    block: UiBlock;
}>();

const { loadComponent } = useUiComponentLoader();

// Load the component based on ui_type
const dynamicComponent = computed<Component | null>(() => {
    return loadComponent(props.block.ui_type);
});

const hasComponent = computed(() => dynamicComponent.value !== null);

onMounted(() => {
    console.log(props.block);
});
</script>

<template>
    <div class="ui-block-wrapper mb-8">
        <!-- Title and Description (common for all UI types) -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-foreground mb-1">
                {{ block.title }}
            </h3>
            <p v-if="block.description" class="text-sm text-muted-foreground">
                {{ block.description }}
            </p>
        </div>

        <!-- Dynamic Component Content -->
        <div v-if="hasComponent" class="ui-block-content py-2">
            <component 
                :is="dynamicComponent" 
                v-bind="block.data"
            />
        </div>

        <!-- Fallback for unknown UI types -->
        <div v-else class="p-4 rounded-lg bg-destructive/10 border border-destructive/20">
            <p class="text-sm text-destructive">
                Unknown UI type: <code class="font-mono">{{ block.ui_type }}</code>
            </p>
            <details class="mt-2">
                <summary class="text-xs text-muted-foreground cursor-pointer">View raw data</summary>
                <pre class="mt-2 text-xs overflow-x-auto">{{ JSON.stringify(block.data, null, 2) }}</pre>
            </details>
        </div>
    </div>
</template>

<style scoped>
.ui-block-wrapper {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ui-block-content {
    background-color: var(--card);
    border-radius: var(--radius);
}
</style>
