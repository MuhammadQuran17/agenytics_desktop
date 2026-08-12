import { watch, nextTick, onMounted, computed, type Ref } from 'vue';
import { useAiChatStore } from '@/store/aiChatStore';

export function useScrollToMessage(messageRefs: Ref<HTMLElement[]>) {
    const aiChatStore = useAiChatStore();
    const messages = computed(() => aiChatStore.currentChatHistory);

    const scrollToMessage = (index: number) => {
        nextTick(() => {
            messageRefs.value[index]?.scrollIntoView({ behavior: 'smooth' });
        });
    };

    const scrollToLastMessage = () => {
        if (messages.value.length > 0) {
            scrollToMessage(messages.value.length - 1);
        }
    };

    watch(messages, scrollToLastMessage, { deep: true });

    onMounted(scrollToLastMessage);
}