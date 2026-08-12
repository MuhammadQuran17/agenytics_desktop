<script setup lang="ts">
import { computed } from 'vue';
import VChart from "vue-echarts";
import * as echarts from 'echarts/core';
import { TooltipComponent, LegendComponent } from 'echarts/components';
import { PieChart } from 'echarts/charts';
import { LabelLayout } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    TooltipComponent,
    LegendComponent,
    PieChart,
    CanvasRenderer,
    LabelLayout
]);

interface ChartData {
    series: {
        name: string;
        value: number
    }[];
};  

const props = defineProps<ChartData>();

console.log(props);

const option = computed(() => {
    return {
        tooltip: {
            trigger: 'item'
        },
        legend: {
            orient: 'vertical',
            left: 'left'
        },
        series: [
            {
                type: 'pie',
                radius: '70%',
                data: props.series,
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };
});
</script>

<template>
    <VChart class="chart" :option="option" />
</template>

<style scoped>
.chart {
    height: 400px;
}
</style>