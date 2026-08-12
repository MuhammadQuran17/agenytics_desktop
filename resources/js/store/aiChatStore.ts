import { defineStore } from 'pinia'
import type { Message } from '@/types/chat'

interface PollingJob {
  jobId: string
  timestamp: number
}

interface PollingState {
  [sessionId: string]: PollingJob
}

const POLLING_STORAGE_KEY = 'ai-chat-polling-jobs'
const MAX_JOB_AGE_MS = 60 * 60 * 1000 // 1 hour

export const useAiChatStore = defineStore('ai-chat', {
  state: () => {
    return {
        currentChatHistory: [] as Message[],
        isLoading: false,
        processingSessionIds: new Set<string>(),
        activePollingJobs: new Map<string, string>(), // sessionId -> jobId
    }
  },
  getters: {
    hasActiveChatHistory: (state) => state.currentChatHistory.length > 0,
    isSessionProcessing: (state) => (sessionId: string) => state.processingSessionIds.has(sessionId),
  },
  actions: {
    setChatHistory(history: Message[]) {
      this.currentChatHistory = history
    },
    setLoading(loading: boolean) {
      this.isLoading = loading
    },
    addMessage(message: Message) {
      this.currentChatHistory.push(message)
    },
    addProcessingSession(sessionId: string) {
      this.processingSessionIds.add(sessionId)
    },
    removeProcessingSession(sessionId: string) {
      this.processingSessionIds.delete(sessionId)
    },
    
    // [START] Polling job management with localStorage persistence
    startPollingForSession(sessionId: string, jobId: string) {
      this.activePollingJobs.set(sessionId, jobId)
      this.addProcessingSession(sessionId)
      this.savePollingStateToStorage()
    },
    
    stopPollingForSession(sessionId: string) {
      this.activePollingJobs.delete(sessionId)
      this.removeProcessingSession(sessionId)
      this.savePollingStateToStorage()
    },
    
    getActiveJobForSession(sessionId: string): string | undefined {
      return this.activePollingJobs.get(sessionId)
    },
    
    cleanupCompletedJob(sessionId: string) {
      this.stopPollingForSession(sessionId)
    },
    
    loadPollingStateFromStorage() {
      try {
        const stored = localStorage.getItem(POLLING_STORAGE_KEY)
        if (!stored) return
        
        const pollingState: PollingState = JSON.parse(stored)
        const now = Date.now()
        
        // Load jobs and clean up old ones
        Object.entries(pollingState).forEach(([sessionId, job]) => {
          if (now - job.timestamp < MAX_JOB_AGE_MS) {
            this.activePollingJobs.set(sessionId, job.jobId)
            this.addProcessingSession(sessionId)
          }
        })
        
        // Save cleaned up state back
        this.savePollingStateToStorage()
      } catch (error) {
        console.error('Failed to load polling state from storage:', error)
      }
    },
    
    savePollingStateToStorage() {
      try {
        const pollingState: PollingState = {}
        const now = Date.now()
        
        this.activePollingJobs.forEach((jobId, sessionId) => {
          pollingState[sessionId] = {
            jobId,
            timestamp: now
          }
        })
        
        localStorage.setItem(POLLING_STORAGE_KEY, JSON.stringify(pollingState))
      } catch (error) {
        console.error('Failed to save polling state to storage:', error)
      }
    },
    // [END] Polling job management with localStorage persistence
  },
})