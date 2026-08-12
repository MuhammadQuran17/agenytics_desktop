<script setup lang="ts">
import { AlertTriangle, Info, CheckCircle, XCircle } from 'lucide-vue-next'

type AlertVariant = 'default' | 'destructive' | 'warning' | 'info' | 'success'

interface Props {
  variant?: AlertVariant
  title?: string
  description?: string
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  class: '',
})

const variantClasses = {
  default: 'bg-muted text-muted-foreground border-border',
  destructive: 'bg-destructive/10 text-destructive border-destructive/20',
  warning: 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-200 dark:border-yellow-800',
  info: 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-200 dark:border-blue-800',
  success: 'bg-green-50 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-200 dark:border-green-800',
}

const iconComponents = {
  default: Info,
  destructive: XCircle,
  warning: AlertTriangle,
  info: Info,
  success: CheckCircle,
}
</script>

<template>
  <div
    :class="[
      'flex items-start gap-3 rounded-lg border p-4 text-sm',
      variantClasses[variant],
      props.class
    ]"
  >
    <component :is="iconComponents[variant]" class="h-5 w-5 flex-shrink-0 mt-0.5" />
    <div class="flex-1">
      <div v-if="title" class="font-medium mb-1">{{ title }}</div>
      <div v-if="description" class="text-sm opacity-90">{{ description }}</div>
    </div>
  </div>
</template>