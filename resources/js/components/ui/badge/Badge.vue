<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const classes = computed(() => {
  const baseClasses = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2'

  switch (props.variant) {
    case 'secondary':
      return cn(baseClasses, 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/37')
    case 'destructive':
      return cn(baseClasses, 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/37')
    case 'outline':
      return cn(baseClasses, 'text-foreground')
    default:
      return cn(baseClasses, 'border-transparent bg-primary text-primary-foreground hover:bg-primary/37')
  }
})
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>