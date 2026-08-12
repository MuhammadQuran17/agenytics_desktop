<script setup lang="ts">
import * as echarts from 'echarts/core';
import {
  DatasetComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
} from 'echarts/components';
import { LineChart } from 'echarts/charts';
import { UniversalTransition } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';
import VChart from "vue-echarts";
import { computed } from 'vue';

echarts.use([
  DatasetComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  LineChart,
  CanvasRenderer,
  UniversalTransition
]);

interface LineChartData {
  x_axis: {
    values: string[];
  };
  series: {
    name: string;
    data: number[][];
  }[];
}

const props = defineProps<LineChartData>();
  
const option = computed(() => {
  return {
    legend: {},
    tooltip: {
      trigger: 'axis',
      showContent: false
    },
    dataset: {
      source: [
        ['x', ...props.x_axis.values],
        ...props.series.map(s => [s.name, ...s.data])
      ]
    },
    xAxis: { type: 'category' },
    yAxis: { gridIndex: 0 },
    series: props.series.map(() => ({
      type: 'line',
      smooth: true,
      seriesLayoutBy: 'row',
      emphasis: { 
        focus: 'series',
        label: {
          show: true,
          position: 'top'
        }
      },
      label: {
        show: false
      }
    }))
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