<script setup lang="ts">
import { computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import DropdownMenuSeparator from '@/components/ui/dropdown-menu/DropdownMenuSeparator.vue';
import DropdownMenuGroup from '@/components/ui/dropdown-menu/DropdownMenuGroup.vue';
import { useAiConfigurationStore } from '@/store/aiConfigurationStore';

const aiProviders = [
    {
        name: 'openai', 
        models: ['o1', 'o3-mini', 'gpt-4.1', 'gpt-4.1-mini', 'gpt-4.1-nano']
    }
];

const { selectedAiProvider, selectedModel } = useAiConfigurationStore();

const models = computed(() => {
  const provider = aiProviders.find(p => p.name === selectedAiProvider.value);
  return provider ? provider.models : [];
});
</script>

<template>
    <!-- AI Provider Dropdown -->
    <div>
        <DropdownMenu>
            <DropdownMenuTrigger class="w-full">
                <div
                    class="flex items-center gap-1 justify-between px-4 py-2 bg-background border border-border rounded-md hover:bg-muted transition-colors">
                    <span class="text-sm text-foreground">
                        {{ selectedAiProvider ?? 'Select AI Provider' }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-muted-foreground" />
                </div>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-full min-w-44">
                <DropdownMenuGroup v-for="provider in aiProviders" :key="provider.name">
                    <DropdownMenuItem @click="selectedAiProvider = provider.name; selectedModel = null"
                        class="cursor-pointer hover:bg-muted text-sm">
                        {{ provider.name }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="provider.name != aiProviders[aiProviders.length - 1].name" />
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>

    <!-- Models Dropdown -->
    <div v-if="selectedAiProvider">
        <DropdownMenu>
            <DropdownMenuTrigger class="w-full">
                <div
                    class="flex items-center gap-1 justify-between px-4 py-2 bg-background border border-border rounded-md hover:bg-muted transition-colors">
                    <span class="text-sm text-foreground">
                        {{ selectedModel ?? 'Select Model' }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-muted-foreground" />
                </div>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-full  min-w-34">
                <DropdownMenuGroup v-for="model in models" :key="model">
                    <DropdownMenuItem @click="selectedModel = model"
                        class="cursor-pointer hover:bg-muted text-sm">
                        {{ model }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator v-if="model != models[models.length - 1]" />
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template> 