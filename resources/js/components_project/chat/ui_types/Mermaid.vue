<script setup lang="ts">
import { VueMermaidRender } from 'vue-mermaid-render'

const props = defineProps<{
    syntax: string;
}>();
</script>

<template>
  <VueMermaidRender :content="props.syntax" />
</template>