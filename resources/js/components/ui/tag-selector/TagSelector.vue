<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { ChevronDown, X } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'
import DropdownMenuSeparator from '@/components/ui/dropdown-menu/DropdownMenuSeparator.vue'
import Input from '@/components/ui/input/Input.vue'

interface Props {
  modelValue: string
  placeholder?: string
  disabled?: boolean
  class?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select or type a tag',
  disabled: false,
  class: '',
})

const emit = defineEmits<Emits>()

const defaultTags = ['feature', 'bug', 'question']
const isOpen = ref(false)
const searchInput = ref('')
const inputRef = ref<HTMLInputElement>()

const filteredTags = computed(() => {
  const search = searchInput.value.toLowerCase()
  return defaultTags.filter(tag => tag.toLowerCase().includes(search))
})

const showCustomOption = computed(() => {
  const search = searchInput.value.trim()
  return search && !defaultTags.some(tag => tag.toLowerCase() === search.toLowerCase())
})

const selectTag = (tag: string) => {
  emit('update:modelValue', tag)
  searchInput.value = ''
  isOpen.value = false
}

const selectCustomTag = () => {
  const customTag = searchInput.value.trim()
  if (customTag) {
    emit('update:modelValue', customTag)
    searchInput.value = ''
    isOpen.value = false
  }
}

const clearSelection = () => {
  emit('update:modelValue', '')
}

const handleInput = () => {
  // If user types and presses enter, treat as custom tag
  if (searchInput.value.trim()) {
    selectCustomTag()
  }
}

const handleOpenChange = (open: boolean) => {
  isOpen.value = open
  if (open) {
    nextTick(() => {
      inputRef.value?.focus()
    })
  } else {
    searchInput.value = ''
  }
}
</script>

<template>
  <DropdownMenu :open="isOpen" @update:open="handleOpenChange">
    <DropdownMenuTrigger class="w-full">
      <div
        :class="[
          'flex items-center justify-between px-3 py-2 h-10 w-full rounded-md border border-input bg-background text-sm ring-offset-background',
          'hover:bg-accent hover:text-accent-foreground',
          'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
          'disabled:cursor-not-allowed disabled:opacity-50',
          props.class
        ]"
        :disabled="props.disabled"
      >
        <span class="truncate">
          {{ modelValue || placeholder }}
        </span>
        <div class="flex items-center gap-1">
          <button
            v-if="modelValue"
            @click.stop="clearSelection"
            class="h-4 w-4 rounded-full hover:bg-muted flex items-center justify-center"
          >
            <X class="h-3 w-3" />
          </button>
          <ChevronDown class="h-4 w-4 opacity-50 flex-shrink-0" />
        </div>
      </div>
    </DropdownMenuTrigger>

    <DropdownMenuContent class="w-full min-w-70 p-2">
      <div class="mb-2">
        <Input
          ref="inputRef"
          v-model="searchInput"
          placeholder="Search or type custom tag..."
          class="h-8"
          @keydown.enter="handleInput"
        />
      </div>

      <DropdownMenuSeparator />

      <!-- Default Tags -->
      <div v-if="filteredTags.length" class="mb-2">
        <div class="px-2 py-1 text-xs font-medium text-muted-foreground">Default Tags</div>
        <DropdownMenuItem
          v-for="tag in filteredTags"
          :key="tag"
          @click="selectTag(tag)"
          class="cursor-pointer"
        >
          {{ tag }}
        </DropdownMenuItem>
      </div>

      <!-- Custom Tag Option -->
      <div v-if="showCustomOption" class="border-t pt-2">
        <DropdownMenuItem
          @click="selectCustomTag"
          class="cursor-pointer text-primary"
        >
          Add "{{ searchInput.trim() }}"
        </DropdownMenuItem>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>