<script setup lang="ts">
import { ref } from 'vue';
import { ArrowUp } from 'lucide-vue-next';
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import ChatSuggestions from './ChatSuggestions.vue';

const emit = defineEmits<{
    (e: 'send', message: string): void;
}>();

const newMessage = ref('');

const sendMessage = () => {
    if (!newMessage.value) return;

    emit('send', newMessage.value);
    newMessage.value = '';
};

const handleSuggestionSelect = (message: string) => {
    newMessage.value = message;
    sendMessage();
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <div class="max-w-3xl sticky bottom-3 z-50 mx-auto w-full px-4 md:px-0">
        <ChatSuggestions @select="handleSuggestionSelect" />

        <div class="relative">
            <Textarea
                data-testid="multimodal-input"
                v-model.trim="newMessage"
                :class="[
                    'min-h-[24px] max-h-[calc(75dvh)] overflow-hidden resize-none',
                    'rounded-3xl bg-white pb-14 pt-4 px-5 dark:border-zinc-700 w-full',
                    'border-2 focus:outline-none focus:ring-0'
                ]"
                placeholder="Send a message..."
                @keydown="handleKeyDown"
            />

            <!-- Send Message Button -->
            <div class="absolute bottom-2 right-1 px-2">
                <Button @click="sendMessage" :disabled="!newMessage" class="rounded-[50%] p-1 cursor-pointer">
                    <ArrowUp class="h-5 w-5"/>
                </Button>
            </div>
        </div>
    </div>
</template> 