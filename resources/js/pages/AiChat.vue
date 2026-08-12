<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ChatBot from "@/components_project/chat/ChatBot.vue";
import { toRef, provide, watch } from 'vue';
import { useAiChatStore } from '@/store/aiChatStore';

const props = defineProps<{
    currentChatSessionId: string;
    chatHistory: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'AI Chat',
        href: '/chat',
    },
];

// Provide reactive references so child components can watch for changes
provide('currentChatSessionId', toRef(props, 'currentChatSessionId'));

const aiChatStore = useAiChatStore();

// Watch for chat history changes and update store
// This fires both on mount and when switching chats
watch(() => props.chatHistory, (newHistory) => {
    aiChatStore.setChatHistory(newHistory);
}, { immediate: true });

</script>

<template>
    <Head title="AI Chat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 mt-10">
            <ChatBot />
        </div>
    </AppLayout>
</template>
