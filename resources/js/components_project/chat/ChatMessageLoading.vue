<template>
  <div class="space-y-6">
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