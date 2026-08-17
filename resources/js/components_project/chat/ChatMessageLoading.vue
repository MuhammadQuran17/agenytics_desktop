<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import type { ProgressStep } from '@/store/aiChatStore';

defineProps<{
  steps?: ProgressStep[];
}>();
</script>

<template>
  <div class="space-y-1.5">
    <template v-if="steps && steps.length">
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
    </template>

    <div v-else class="flex items-center gap-2 text-sm text-muted-foreground">
      <span class="relative flex h-2 w-2 shrink-0 ml-1 mr-1">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
      </span>
      <span>Thinking...</span>
    </div>
  </div>
</template>
