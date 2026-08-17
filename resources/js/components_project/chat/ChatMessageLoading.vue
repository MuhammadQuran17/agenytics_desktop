<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import type { ProgressStep } from '@/store/aiChatStore';

defineProps<{
  steps?: ProgressStep[];
}>();
</script>

<template>
  <div class="space-y-6">
    <div v-if="steps && steps.length" class="space-y-1.5 mb-3">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="flex items-center gap-2 text-sm"
        :class="step.status === 'done' ? 'text-muted-foreground' : 'text-foreground'"
      >
        <span v-if="step.status === 'done'" class="flex h-4 w-4 items-center justify-center shrink-0">
          <Check class="h-3.5 w-3.5" />
        </span>
        <span v-else class="relative flex h-2 w-2 shrink-0 ml-1 mr-1">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
        </span>
        <span>{{ step.message }}</span>
      </div>
    </div>

    <div class="bg-gray-100 p-4 rounded-md animate-pulse">
      <div class="flex items-center space-x-2 mb-8">
        <div class="h-4 bg-gray-300 rounded w-5/6"></div>
      </div>
      <div class="space-y-3">
        <div v-for="j in 2" :key="j" class="flex flex-col space-y-2 mb-5">
          <div class="h-4 bg-gray-300 rounded w-24"></div>
          <div class="h-3 bg-gray-300 rounded w-full"></div>
          <div class="h-3 bg-gray-300 rounded w-5/6"></div>
          <div class="h-3 bg-gray-300 rounded w-4/5"></div>
        </div>
      </div>
    </div>
  </div>
</template>
