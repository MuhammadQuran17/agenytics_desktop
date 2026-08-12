<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'

interface SelectOption {
  value: string
  label: string
  disabled?: boolean
}

interface Props {
  modelValue: string
  options: SelectOption[]
  placeholder?: string
  disabled?: boolean
  class?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option',
  disabled: false,
  class: '',
})

const emit = defineEmits<Emits>()

const selectedOption = computed(() => {
  return props.options.find(option => option.value === props.modelValue)
})

const selectOption = (option: SelectOption) => {
  if (!option.disabled) {
    emit('update:modelValue', option.value)
  }
}
</script>

<template>
  <DropdownMenu :disabled="props.disabled">
    <DropdownMenuTrigger :disabled="props.disabled" as-child>
      <div
        :class="[
          'flex items-center justify-between px-3 py-2 h-10 w-full rounded-md border border-input bg-background text-sm ring-offset-background',
          'hover:bg-accent hover:text-accent-foreground',
          'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
          'disabled:cursor-not-allowed disabled:opacity-50',
          { 'cursor-not-allowed opacity-50 pointer-events-none': props.disabled },
          props.class
        ]"
      >
        <span class="truncate">
          {{ selectedOption?.label || placeholder }}
        </span>
        <ChevronDown class="h-4 w-4 opacity-50 flex-shrink-0" />
      </div>
    </DropdownMenuTrigger>
    <DropdownMenuContent v-if="!props.disabled" class="w-full min-w-70">
      <DropdownMenuItem
        v-for="option in options"
        :key="option.value"
        @click="selectOption(option)"
        :disabled="option.disabled"
        class="cursor-pointer"
      >
        {{ option.label }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>