import { defineStore } from 'pinia'
import type { Message } from '@/types/chat'

interface PollingJob {
  jobId: string
  timestamp: number
}

export interface ProgressStep {
  message: string
  status: 'in_progress' | 'done'
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
        progressSteps: new Map<string, ProgressStep[]>(), // sessionId -> ordered tool-call log
        stalledSessions: new Set<string>(), // sessionId -> polling gave up on a network outage, job may still be running
        failedSessions: new Map<string, string>(), // sessionId -> error message from a job that failed server-side (e.g. the AI provider was unreachable)
    }
  },
  getters: {
    hasActiveChatHistory: (state) => state.currentChatHistory.length > 0,
    isSessionProcessing: (state) => (sessionId: string) => state.processingSessionIds.has(sessionId),
    getProgressSteps: (state) => (sessionId: string) => state.progressSteps.get(sessionId) ?? [],
    isSessionStalled: (state) => (sessionId: string) => state.stalledSessions.has(sessionId),
    getSessionFailure: (state) => (sessionId: string) => state.failedSessions.get(sessionId),
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

    setMessageRating(jobId: string, rating: 'good' | 'bad' | null) {
      const message = this.currentChatHistory.find((m) => m.role === 'assistant' && m.jobId === jobId)
      if (message) {
        message.rating = rating
      }
    },

    addProcessingSession(sessionId: string) {
      this.processingSessionIds.add(sessionId)
    },
    removeProcessingSession(sessionId: string) {
      this.processingSessionIds.delete(sessionId)
    },
    
    setProgressSteps(sessionId: string, steps: ProgressStep[] | null | undefined) {
      if (steps && steps.length > 0) {
        this.progressSteps.set(sessionId, steps)
      } else {
        this.progressSteps.delete(sessionId)
      }
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
      this.progressSteps.delete(sessionId)
      this.savePollingStateToStorage()
    },
    
    getActiveJobForSession(sessionId: string): string | undefined {
      return this.activePollingJobs.get(sessionId)
    },

    cleanupCompletedJob(sessionId: string) {
      this.stopPollingForSession(sessionId)
    },

    // Polling gave up after a prolonged network outage, but the job itself
    // (and its localStorage entry) is deliberately left in place so "Continue"
    // can resume checking on it instead of losing track of the in-flight answer.
    markSessionStalled(sessionId: string) {
      this.stalledSessions.add(sessionId)
    },

    clearSessionStalled(sessionId: string) {
      this.stalledSessions.delete(sessionId)
    },

    // The job reached the server and ran, but ultimately failed (e.g. the AI
    // provider was unreachable because the internet dropped mid-request).
    // Kept visible in the chat itself, not just a toast, so it survives being missed.
    markSessionFailed(sessionId: string, message: string) {
      this.failedSessions.set(sessionId, message)
    },

    clearSessionFailed(sessionId: string) {
      this.failedSessions.delete(sessionId)
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