<script setup lang="ts">
import { ref, inject, computed, type Ref } from 'vue';
import { usePage } from "@inertiajs/vue3";
import type { SharedData, User } from "@/types";
import axios from 'axios';
import ChatInput from '@/components_project/chat/ChatInput.vue';
import { useAiChatStore } from '@/store/aiChatStore';
import { toast } from 'vue-sonner';
import { Check } from 'lucide-vue-next';
import ChatMessageLoading from '@/components_project/chat/ChatMessageLoading.vue';
import { marked } from 'marked';
import { useChatPolling } from '@/composables/useChatPolling';
import { useScrollToMessage } from '@/composables/useScrollToMessage';
import UiBlockWrapper from '@/components_project/chat/UiBlockWrapper.vue';

interface UiBlock {
    ui_type: string;
    title: string;
    description: string;
    data: any;
}

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const currentChatSessionId = inject<Ref<string> | undefined>('currentChatSessionId');

const messageRefs = ref<HTMLElement[]>([]);

const aiChatStore = useAiChatStore();
const messages = computed(() => aiChatStore.currentChatHistory);
const hasActiveChatHistory = computed(() => aiChatStore.hasActiveChatHistory);
const isLoading = computed(() => currentChatSessionId?.value ? aiChatStore.isSessionProcessing(currentChatSessionId.value) : false);
const progressSteps = computed(() => currentChatSessionId?.value ? aiChatStore.getProgressSteps(currentChatSessionId.value) : []);

// [START] Scroll to message
useScrollToMessage(messageRefs)
// [END] Scroll to message

// [START] Handle send message
const handleSendMessage = async (message: string, pdfs: File[] = []) => {
    try {
        aiChatStore.addMessage({
            content: message,
            role: 'user',
            created_at: new Date().toISOString(),
        });

        const formData = new FormData();
        formData.append('message', message);
        formData.append('sessionId', currentChatSessionId?.value ?? '');
        pdfs.forEach((pdf) => formData.append('pdfs[]', pdf));

        const response = await axios.post(route('chat.send'), formData);

        const { jobId } = response.data;

        // Start polling for job status
        if (currentChatSessionId?.value) {
            startPolling(currentChatSessionId.value, jobId);
        }

    } catch (error: any) {
        toast.error(error.response?.data?.message || 'An error occurred');

        if (currentChatSessionId?.value) {
            aiChatStore.stopPollingForSession(currentChatSessionId.value);
        }
    }
};
// [END] Handle send message

// [START] Polling state - session-aware
const { startPolling } = useChatPolling({
    currentChatSessionId,
    messages,
});
// [END] Polling state - session-aware

// [START] Parse UI Blocks from AI response
/**
 * Check if the message content is a JSON array of UI blocks
 */
const parseUiBlocks = (content: string | any): UiBlock[] | null => {
    if (!content) {
        return null;
    }

    try {
        // If content is already an object/array, use it directly
        if (typeof content === 'object') {
            return Array.isArray(content) ? content : null;
        }

        // Try to parse as JSON string
        const trimmed = content.trim();
        if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
            const parsed = JSON.parse(trimmed);
            if (Array.isArray(parsed) && parsed.every(item => item.ui_type)) {
                return parsed;
            }
        }
    } catch (error) {
        // Not a valid JSON, return null to fall back to markdown
        return null;
    }

    return null;
};

/**
 * Determine if a message contains UI blocks
 */
const isUiBlockMessage = (message: any): boolean => {
    return parseUiBlocks(message.content) !== null;
};
// [END] Parse UI Blocks from AI response
</script>

<template>
    <div class="flex h-full flex-col relative">
        <div ref="chatContainer" class="flex-1 overflow-y-auto py-8 px-4 md:px-0">
            <div class="max-w-4xl mx-auto">
                <div v-if="!hasActiveChatHistory" class="text-center mb-16">
                    <h1 class="text-3xl font-medium text-foreground mb-4">Hello {{ user.name }}!</h1>
                    <p class="text-xl text-muted-foreground">What we should do today?</p>
                </div>

                <template v-else>
                    <template v-for="(message, index) in messages" :key="index">
                        <!-- Assistant messages -->
                        <div 
                            v-if="message.role === 'assistant'" 
                            class="mb-16 max-w-full"
                            :ref="(el: any) => { if (el) messageRefs[index] = el as HTMLElement }"
                        >
                            <!-- Tool-call log recorded while this answer was produced -->
                            <details v-if="message.steps && message.steps.length" class="mb-4 text-sm text-muted-foreground">
                                <summary class="cursor-pointer select-none w-fit">Steps taken ({{ message.steps.length }})</summary>
                                <div class="space-y-1.5 mt-2">
                                    <div v-for="(step, stepIndex) in message.steps" :key="stepIndex" class="flex items-center gap-2">
                                        <span class="flex h-4 w-4 items-center justify-center shrink-0">
                                            <Check class="h-3.5 w-3.5" />
                                        </span>
                                        <span>{{ step.message }}</span>
                                    </div>
                                </div>
                            </details>

                            <!-- UI Blocks Strategy Pattern -->
                            <div v-if="isUiBlockMessage(message)" class="ui-blocks-container">
                                <UiBlockWrapper
                                    v-for="(block, blockIndex) in parseUiBlocks(message.content)"
                                    :key="blockIndex"
                                    :block="block"
                                />
                            </div>

                            <!-- Fallback to Markdown rendering -->
                            <div
                                v-else
                                class="markdown-content"
                                v-html="message.content && marked.parse(message.content as string)"
                            ></div>
                        </div>

                        <!-- User messages -->
                        <div 
                            v-else 
                            class="w-full"
                            :ref="(el: any) => { if (el) messageRefs[index] = el as HTMLElement }"
                        >
                            <div class="mb-6 ml-auto w-fit text-right bg-muted rounded-3xl py-2.5 px-5 text-medium">
                                <div class="text-secondary-foreground">{{ message.content }}</div>
                            </div>
                        </div>
                    </template>

                    <ChatMessageLoading v-if="isLoading" :steps="progressSteps" />
                </template>

            </div>
        </div>

        <ChatInput @send="handleSendMessage" />
    </div>
</template>

<style scoped>
.fade-enter-active {
  transition: opacity 1s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

.markdown-content {
  color: var(--foreground);
  line-height: 1.75;
}

.markdown-content :deep(h1),
.markdown-content :deep(h2),
.markdown-content :deep(h3),
.markdown-content :deep(h4),
.markdown-content :deep(h5),
.markdown-content :deep(h6) {
  font-weight: 600;
  margin-top: 1.5em;
  margin-bottom: 0.5em;
  line-height: 1.25;
}

.markdown-content :deep(h1) {
  font-size: 2em;
}

.markdown-content :deep(h2) {
  font-size: 1.5em;
}

.markdown-content :deep(h3) {
  font-size: 1.25em;
}

.markdown-content :deep(p) {
  margin-top: 1em;
  margin-bottom: 1em;
}

.markdown-content :deep(ul),
.markdown-content :deep(ol) {
  margin-top: 1em;
  margin-bottom: 1em;
  padding-left: 2em;
}

.markdown-content :deep(li) {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.markdown-content :deep(strong) {
  font-weight: 600;
}

.markdown-content :deep(em) {
  font-style: italic;
}

.markdown-content :deep(code) {
  background-color: var(--muted);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-family: var(--font-mono);
  font-size: 0.875em;
}

.markdown-content :deep(pre) {
  background-color: var(--muted);
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
  margin-top: 1em;
  margin-bottom: 1em;
}

.markdown-content :deep(pre code) {
  background-color: transparent;
  padding: 0;
}

.markdown-content :deep(blockquote) {
  border-left: 4px solid var(--border);
  padding-left: 1em;
  margin-left: 0;
  margin-top: 1em;
  margin-bottom: 1em;
  color: var(--muted-foreground);
  font-style: italic;
}

.markdown-content :deep(a) {
  color: var(--primary);
  text-decoration: underline;
}

.markdown-content :deep(a:hover) {
  color: var(--primary-foreground);
}
</style>