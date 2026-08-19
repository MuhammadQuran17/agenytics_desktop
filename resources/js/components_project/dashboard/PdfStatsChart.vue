<script setup lang="ts">
import { BarChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent } from 'echarts/components';
import * as echarts from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import VChart from 'vue-echarts';

echarts.use([BarChart, GridComponent, LegendComponent, TooltipComponent, CanvasRenderer]);

// Fixed status colors (good / critical) — deliberately mode-invariant, see the
// dataviz skill's palette.md. Never repurposed for anything but success/failure.
const SUCCESS_COLOR = '#0ca30c';
const FAILED_COLOR = '#d03b3b';

const props = defineProps<{
    labels: string[];
    fullLabels: string[];
    currentIndex: number;
    success: number[];
    failed: number[];
}>();

// ECharts needs literal color strings, not `var(...)`, so read the app's
// current theme tokens and recompute whenever the `dark` class flips.
const themeTick = ref(0);
let observer: MutationObserver | undefined;

onMounted(() => {
    observer = new MutationObserver(() => themeTick.value++);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onBeforeUnmount(() => observer?.disconnect());

const cssVar = (name: string): string => {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
};

const option = computed(() => {
    void themeTick.value; // recompute whenever the `dark` class flips

    const mutedForeground = cssVar('--muted-foreground');
    const border = cssVar('--border');
    const foreground = cssVar('--foreground');

    return {
        legend: {
            top: 0,
            left: 0,
            textStyle: { color: foreground },
            icon: 'roundRect',
        },
        grid: {
            top: 40,
            left: 8,
            right: 8,
            bottom: 8,
            containLabel: true,
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: (params: Array<{ dataIndex: number; marker: string; seriesName: string; value: number }>) => {
                if (params.length === 0) {
                    return '';
                }

                const header = props.fullLabels[params[0].dataIndex] ?? props.labels[params[0].dataIndex];
                const rows = params.map((p) => `${p.marker} ${p.seriesName}: <strong>${p.value}</strong>`).join('<br/>');

                return `<div style="font-weight:600;margin-bottom:4px;">${header}</div>${rows}`;
            },
        },
        xAxis: {
            type: 'category',
            data: props.labels,
            axisTick: { alignWithLabel: true },
            axisLine: { lineStyle: { color: border } },
            axisLabel: {
                color: mutedForeground,
                rich: { current: { color: foreground, fontWeight: 700 } },
                formatter: (value: string, index: number) => (index === props.currentIndex ? `{current|${value}}` : value),
                // A custom formatter makes ECharts' automatic label-thinning
                // unreliable, and "Today" is the one label that must never
                // be the one it drops — so pick the thinning step ourselves.
                interval: (index: number) => index === props.currentIndex || index % (props.labels.length > 6 ? 2 : 1) === 0,
            },
        },
        yAxis: {
            type: 'value',
            minInterval: 1,
            splitLine: { lineStyle: { color: border, type: 'solid' } },
            axisLabel: { color: mutedForeground },
        },
        series: [
            {
                name: 'Success',
                type: 'bar',
                data: props.success,
                color: SUCCESS_COLOR,
                barMaxWidth: 24,
                itemStyle: { borderRadius: [4, 4, 0, 0] },
            },
            {
                name: 'Failed',
                type: 'bar',
                data: props.failed,
                color: FAILED_COLOR,
                barMaxWidth: 24,
                itemStyle: { borderRadius: [4, 4, 0, 0] },
            },
        ],
    };
});
</script>

<template>
    <VChart class="chart" :option="option" autoresize />
</template>

<style scoped>
.chart {
    height: 320px;
    width: 100%;
}
</style>
