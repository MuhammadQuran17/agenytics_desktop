<script setup lang="ts">
import CodeEditor from 'simple-code-editor';
import { computed } from 'vue';

interface Props {
    code: string;
    language?: string;
    highlight_lines?: number[];
    show_line_numbers?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    language: 'plaintext',
    show_line_numbers: true,
});

const code = computed(() => {
    return props.code.replace(/\\n/g, '\n');
});

</script>

<template>
    <CodeEditor theme="atom-one-dark-reasonable" width="100%" :read-only="true" :value="code"
        :languages="[[props.language]]" :line-nums="props.show_line_numbers">
    </CodeEditor>
</template>