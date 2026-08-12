<script setup lang="ts">
import { ref } from 'vue';
import { useAiChatStore } from '@/store/aiChatStore';

const aiChatStore = useAiChatStore();

const emit = defineEmits<{
    (e: 'select', text: string): void;
}>();

const suggestionButtons = ref([
    {
        id: '1',
        prompt: 'What formula sum all rows in Excel',
    },
    {
        id: '2',
        prompt: 'How to get 2 columns from 2 related tables and save it as a json in third non related one',
    },
]);

const handleSuggestion = (suggestion: { id: string; prompt: string }) => {
    emit('select', suggestion.prompt);
};

</script>

<template>
    <div v-if="suggestionButtons.length > 0 && !aiChatStore.hasActiveChatHistory" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <button
            v-for="button in suggestionButtons"
            :key="button.id"
            @click="handleSuggestion(button)"
            class="text-left px-4 py-3 border border-border rounded-lg hover:bg-muted transition-colors cursor-pointer"
        >
            <div class="text-gray-700 text-sm">{{ button.prompt }}</div>
        </button>
    </div>
</template> 