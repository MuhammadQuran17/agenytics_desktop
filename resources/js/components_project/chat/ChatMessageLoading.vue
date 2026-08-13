<script setup lang="ts">
defineProps<{
  message?: string;
}>();
</script>

<template>
  <div class="space-y-6">
    <div v-if="message" class="flex items-center gap-2 mb-3 text-sm text-muted-foreground">
      <span class="relative flex h-2 w-2">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
      </span>
      <span>{{ message }}</span>
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
