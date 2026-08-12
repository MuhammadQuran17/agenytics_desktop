<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';

interface Props {
    content: string;
}

const props = defineProps<Props>();

// Parse markdown content
const parsedContent = computed(() => {
    if (!props.content) {
        return '';
    }
    return marked.parse(props.content);
});
</script>

<template>
    <div class="text-content">
        <div 
            class="prose prose-sm max-w-none text-foreground"
            v-html="parsedContent"
        ></div>
    </div>
</template>

