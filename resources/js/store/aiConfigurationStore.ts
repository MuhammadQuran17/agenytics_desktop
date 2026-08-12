import { createGlobalState } from '@vueuse/core'
import { ref } from 'vue'

export const useAiConfigurationStore = createGlobalState(() => {
  const isLoading = ref<boolean>(false)

  return {
    isLoading
  }
})
