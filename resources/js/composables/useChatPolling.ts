import { ref, watch, onMounted, onBeforeUnmount, type Ref, type ComputedRef } from 'vue';
import axios from 'axios';
import { useAiChatStore } from '@/store/aiChatStore';
import { toast } from 'vue-sonner';

interface UseChatPollingOptions {
    currentChatSessionId: Ref<string | undefined> | undefined;
    messages: ComputedRef<Array<{ role: string }>>;
}

export function useChatPolling({ currentChatSessionId, messages }: UseChatPollingOptions) {
    const aiChatStore = useAiChatStore();
    
    const pollingIntervalIds = ref<Map<string, number>>(new Map());
    const pollingAttempts = ref<Map<string, number>>(new Map());
    const maxPollingAttempts = 150; // 150 * 4 seconds = 10 minutes max

    const stopPolling = (sessionId: string) => {
        const intervalId = pollingIntervalIds.value.get(sessionId);
        if (intervalId) {
            clearInterval(intervalId);
            pollingIntervalIds.value.delete(sessionId);
            pollingAttempts.value.delete(sessionId);
        }
    };

    const pollJobStatus = async (sessionId: string, jobId: string) => {
        try {
            const response = await axios.post(route('chat.status'), { 
                jobId: String(jobId) 
            });
            const { status, response: aiResponse, error } = response.data;

            if (status === 'completed') {
                stopPolling(sessionId);
                aiChatStore.stopPollingForSession(sessionId);

                if (aiResponse) {
                    aiChatStore.addMessage({
                        content: aiResponse,
                        role: 'assistant',
                        created_at: new Date().toISOString(),
                    });
                }
            } else if (status === 'failed') {
                stopPolling(sessionId);
                aiChatStore.stopPollingForSession(sessionId);
                toast.error(error || 'Processing failed. Please try again.');
            } else if (status === 'processing') {
                // Continue polling
                const attempts = (pollingAttempts.value.get(sessionId) || 0) + 1;
                pollingAttempts.value.set(sessionId, attempts);

                if (attempts >= maxPollingAttempts) {
                    stopPolling(sessionId);
                    aiChatStore.stopPollingForSession(sessionId);
                    toast.error('Processing timeout. Please try again.');
                }
            }
        } catch (error: any) {
            stopPolling(sessionId);
            aiChatStore.stopPollingForSession(sessionId);
            toast.error(error.response?.data?.message || 'Failed to check job status');
        }
    };

    const startPolling = (sessionId: string, jobId: string) => {
        // Stop any existing polling for this session
        stopPolling(sessionId);
        
        pollingAttempts.value.set(sessionId, 0);
        aiChatStore.startPollingForSession(sessionId, jobId);
        
        const intervalId = window.setInterval(() => {
            pollJobStatus(sessionId, jobId);
        }, 4000); // Poll every 4 seconds
        
        pollingIntervalIds.value.set(sessionId, intervalId);
    };

    const resumePollingForSession = (sessionId: string, jobId: string) => {
        // Only resume if not already polling
        if (!pollingIntervalIds.value.has(sessionId)) {
            startPolling(sessionId, jobId);
        }
    };

    const checkAndResumePolling = () => {
        if (!currentChatSessionId?.value) return;
        
        // Load polling state from localStorage
        aiChatStore.loadPollingStateFromStorage();
        
        const activeJobId = aiChatStore.getActiveJobForSession(currentChatSessionId.value);
        
        if (activeJobId) {
            // Check if we already have the assistant response in chatHistory
            const lastMessage = messages.value[messages.value.length - 1];
            
            if (lastMessage && lastMessage.role === 'assistant') {
                // Job completed, clean up
                aiChatStore.cleanupCompletedJob(currentChatSessionId.value);
            } else {
                // Job still processing, resume polling
                resumePollingForSession(currentChatSessionId.value, activeJobId);
            }
        }
    };

    // On mount: check and resume polling if needed
    onMounted(() => {
        checkAndResumePolling();
    });

    // Watch for chat session changes (navigation between chats)
    watch(() => currentChatSessionId?.value, (newSessionId, oldSessionId) => {
        if (!newSessionId) return;
        
        if (oldSessionId && newSessionId !== oldSessionId) {
            // Stop polling interval for old session (but keep job in store)
            stopPolling(oldSessionId);
        }
        
        if (newSessionId) {
            // Check if new session has active job and resume if needed
            const activeJobId = aiChatStore.getActiveJobForSession(newSessionId);
            if (activeJobId) {
                resumePollingForSession(newSessionId, activeJobId);
            }
        }
    });

    // Clean up all polling on component unmount
    onBeforeUnmount(() => {
        // Stop all active polling intervals
        pollingIntervalIds.value.forEach((_, sessionId) => {
            stopPolling(sessionId);
        });
    }); 

    return {
        startPolling,
    };
}

