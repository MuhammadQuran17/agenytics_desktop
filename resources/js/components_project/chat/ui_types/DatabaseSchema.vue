  
  <script lang="ts" setup>
  import { ref, onMounted, computed, watch } from 'vue';
  import RelationGraph from 'relation-graph-vue3';
  import type {
    RGOptions,
    RGNode,
    RGLine,
    RGLink,
    RGUserEvent,
    RGJsonData,
    RelationGraphComponent,
  } from 'relation-graph-vue3';
  
  // Define the component's props
  const props = defineProps({
    backendData: {
      type: Object,
      required: true
    },
  });
  
  const graphRef = ref<RelationGraphComponent | null>(null);
  
  // Graph options for layout and appearance
  const graphOptions: RGOptions = {
    debug: false,
    allowSwitchLineShape: true,
    allowSwitchJunctionPoint: true,
    allowShowDownloadButton: true,
    defaultJunctionPoint: 'border',
    defaultLineMarker: {
      markerWidth: 12,
      markerHeight: 12,
      refX: 6,
      refY: 6,
      data: 'M 0 0, V 12, L 8 6, Z',
    },
    layout: {
      layoutName: 'force',
      center__x: 0,
      center__y: 0,
      force_repulsion: 30000, // Increased repulsion for larger nodes
      force_spring_length: 25000,
      force_layout_gravity: 5,
    },
    moveToCenterWhenRefresh: true,
    zoomToFitWhenRefresh: true,
  };
  
  
  // This computed property transforms the backend data into the format required by RelationGraph.
  const graphData = computed<RGJsonData>(() => {
    const tables = props.backendData.allTablesOfSession;
    if (!tables || Object.keys(tables).length === 0) {
      return { nodes: [], lines: [] };
    }
    const tableNames = Object.keys(tables);
    const foreignKeyMap: Record<string, string[]> = {};
  
    // Logic to automatically detect foreign key relationships by convention (e.g., user_id -> users table)
    const potentialFKs: { fromTable: string; fromColumn: string; toTable: string }[] = [];
    tableNames.forEach(tableName => {
      const tableData = tables[tableName];
      foreignKeyMap[tableName] = [];
      if (tableData && tableData.length > 0) {
          const columns = Object.keys(tableData[0]);
          columns.forEach(columnName => {
              if (columnName.endsWith('_id') && columnName !== 'id') {
                  const potentialTableNameSingular = columnName.slice(0, -3); // e.g., 'category'
                  const potentialTableNamePlural = potentialTableNameSingular + 's'; // 'categories' for 'category'
                  const potentialTableNameIes = (
                    potentialTableNameSingular.endsWith('y')
                      ? potentialTableNameSingular.slice(0, -1) + 'ies'
                      : null
                  ); // handle 'category' -> 'categories'
                  
                  const targetTable = tableNames.find(t =>
                    t === potentialTableNamePlural ||
                    t === potentialTableNameSingular ||
                    (potentialTableNameIes && t === potentialTableNameIes)
                  );
                  
                  if (targetTable) {
                      potentialFKs.push({
                          fromTable: tableName,
                          fromColumn: columnName,
                          toTable: targetTable,
                      });
                      foreignKeyMap[tableName].push(columnName);
                  }
              }
          });
      }
    });
  
    // Create nodes for each table, including all data rows, headers, and FK info
    const nodes: any[] = tableNames.map((tableName, index) => {
      const tableData = tables[tableName] || [];
      const headers = tableData.length > 0 ? Object.keys(tableData[0]) : [];
      return {
          id: tableName,
          text: tableName.charAt(0).toUpperCase() + tableName.slice(1),
          data: {
              rows: tableData,
              headers: headers,
              foreignKeys: foreignKeyMap[tableName] || [],
          },
          x: Math.cos((index / tableNames.length) * 2 * Math.PI) * 500,
          y: Math.sin((index / tableNames.length) * 2 * Math.PI) * 500,
      };
    });
  
    // Create lines for the detected relationships, connecting nodes directly
    const lines: RGLine[] = potentialFKs.map(fk => ({
        from: fk.fromTable,
        to: fk.toTable,
        seeks_id: fk.fromColumn,
        color: 'var(--chart-4)',     
        lineWidth: 3,
        lineShape: 4, // Using a straight line for node-to-node connections
    }));
  
    return { nodes, lines, rootId: tableNames[0] || '' };
  });
  
  // Function to set graph data
  // MOST IMPORTANT function in this component
  const showGraph = async () => {
  const graphInstance = graphRef.value?.getInstance();
  if (graphInstance) {
    // Clear the existing graph completely
    await graphInstance.setJsonData({ nodes: [], lines: [] });
    
    // Small delay to ensure clearing completes
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Now set the new data
    await graphInstance.setJsonData(graphData.value);
    
    // Wait for layout to complete - increased timeout
    await new Promise(resolve => setTimeout(resolve, 600));
    
    // Now center and zoom
    graphInstance.moveToCenter();
    graphInstance.zoomToFit();
  }
};
  
  // Re-render graph when data changes
  watch(() => props.backendData, showGraph, { deep: true, immediate: true });
  
  onMounted(() => {
    showGraph();
    console.log(props.backendData);
  });
  
  // Event handlers
  const onNodeClick = (nodeObject: RGNode, $event: RGUserEvent) => {
    console.log('Node clicked:', nodeObject.text, nodeObject);
  };
  
  const onLineClick = (lineObject: RGLine, linkObject: RGLink, $event: RGUserEvent) => {
    console.log('Line clicked:', lineObject);
  };
  
  // Dynamic styling for nodes using CSS variables
  const getNodeStyle = (node: RGNode) => {
      const chartColors = ['--chart-1', '--chart-2', '--chart-3', '--chart-4', '--chart-5'];
      const colorVar = chartColors[Number(node.index) % chartColors.length];
      return {
          '--node-color': `var(${colorVar})`
      };
  }
  </script>

<template>
    <div class="db-schema-container">
      <RelationGraph
        ref="graphRef"
        :options="graphOptions"
        :on-node-click="onNodeClick"
        :on-line-click="onLineClick"
      >
        <template #node="{ node }">
          <div class="node-container" :style="getNodeStyle(node)">
            <div class="node-header">
              <strong>{{ node.text }}</strong>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th
                      v-for="header in node.data.headers"
                      :key="header"
                      :class="{ 'highlight-fk': node.data.foreignKeys.includes(header) }"
                    >
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, rowIndex) in node.data.rows" :key="rowIndex">
                    <td
                      v-for="header in node.data.headers"
                      :key="`${rowIndex}-${header}`"
                      :class="{ 'highlight-fk': node.data.foreignKeys.includes(header) }"
                    >
                      {{ row[header] }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
      </RelationGraph>
    </div>
  </template>
  
<style scoped>  
  .db-schema-container {
    width: 100%;
    height: 70vh; /* Adjust height as needed */
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background-color: var(--background);
  }
  
  .node-container {
    background-color: var(--card);
    border: 2px solid var(--node-color, var(--border));
    border-radius: var(--radius);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    color: var(--card-foreground);
    overflow: hidden;
    width: 400px; /* Increased width for more data */
    transition: all 0.2s ease-in-out;
  }
  
  .node-header {
    background-color: var(--node-color, var(--primary));
    color: var(--primary-foreground);
    padding: 8px 12px;
    font-size: 1.1rem;
  }
  
  .table-wrapper {
    max-height: 250px; /* Set a max height for the table body */
    overflow-y: auto; /* Enable vertical scrolling */
    border-top: 1px solid var(--border);
  }
  
  /* Custom scrollbar styling for a better look */
  .table-wrapper::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .table-wrapper::-webkit-scrollbar-track {
    background: var(--muted);
  }
  .table-wrapper::-webkit-scrollbar-thumb {
    background-color: var(--secondary);
    border-radius: 4px;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .data-table th,
  .data-table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    font-size: 0.9rem;
    white-space: nowrap; /* Prevent text wrapping */
  }
  
  .data-table th {
    background-color: var(--muted);
    color: var(--muted-foreground);
    font-weight: 600;
    position: sticky; /* Make headers stick to top on scroll */
    top: 0;
  }
  
  .data-table tr:last-child td {
    border-bottom: none;
  }
  
  .highlight-fk {
    background-color: hsl(43 74% 66% / 0.2); /* Transparent version of --chart-4 for highlighting */
  }
</style>
  