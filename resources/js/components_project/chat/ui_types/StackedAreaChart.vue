<script setup lang="ts">
import { computed } from 'vue';
import * as echarts from 'echarts/core';
import {
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
} from 'echarts/components';
import { LineChart } from 'echarts/charts';
import { UniversalTransition } from 'echarts/features';
import VChart from "vue-echarts";
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  LineChart,
  CanvasRenderer,
  UniversalTransition
]);

interface ChartData {
  x_axis: {
    values: string[];
  };
  series: {
    name: string;
    data: number[];
  }[];
}

const props = defineProps<ChartData>();

const option = computed(() => {
  const legendData = props.series.map(s => s.name);
  const isLastSeries = (index: number) => index === props.series.length - 1;

  return {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        label: {
          backgroundColor: '#6a7985'
        }
      }
    },
    legend: {
      data: legendData
    },
    toolbox: {
      feature: {
        saveAsImage: {}
      }
    },
    xAxis: [
      {
        type: 'category',
        boundaryGap: false,
        data: props.x_axis.values
      }
    ],
    yAxis: [
      {
        type: 'value'
      }
    ],
    series: props.series.map((series, index) => ({
      name: series.name,
      type: 'line',
      stack: 'Total',
      areaStyle: {},
      emphasis: {
        focus: 'series'
      },
      data: series.data
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