<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import PdfStatsChart from '@/components_project/dashboard/PdfStatsChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type PdfStats, type StatsPeriod } from '@/types/dashboard';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckCircle2, FileText, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    period: StatsPeriod;
    pdfStats: PdfStats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const periods: { value: StatsPeriod; label: string }[] = [
    { value: 'day', label: 'Daily' },
    { value: 'week', label: 'Weekly' },
    { value: 'month', label: 'Monthly' },
    { value: 'year', label: 'Yearly' },
];

const activePeriod = ref<StatsPeriod>(props.period);
const stats = ref<PdfStats>(props.pdfStats);
const isLoading = ref(false);

const totalSuccess = computed(() => stats.value.success.reduce((sum, n) => sum + n, 0));
const totalFailed = computed(() => stats.value.failed.reduce((sum, n) => sum + n, 0));

const selectPeriod = async (period: StatsPeriod) => {
    if (period === activePeriod.value) {
        return;
    }

    activePeriod.value = period;
    isLoading.value = true;

    try {
        const response = await axios.get(route('dashboard.pdf-stats'), { params: { period } });
        stats.value = response.data;
    } finally {
        isLoading.value = false;
    }
};

const formatDate = (isoDate: string): string => {
    return new Date(isoDate).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mt-16 flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Filter row: one row, above the content it scopes -->
            <div class="flex items-center gap-2">
                <Button
                    v-for="option in periods"
                    :key="option.value"
                    :variant="activePeriod === option.value ? 'default' : 'outline'"
                    size="sm"
                    @click="selectPeriod(option.value)"
                >
                    {{ option.label }}
                </Button>
            </div>

            <!-- Stat tiles -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-muted-foreground text-sm font-medium">Successful PDFs</CardTitle>
                        <CheckCircle2 class="h-4 w-4" style="color: #0ca30c" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold">{{ totalSuccess.toLocaleString() }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-muted-foreground text-sm font-medium">Failed PDFs</CardTitle>
                        <XCircle class="h-4 w-4" style="color: #d03b3b" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-semibold">{{ totalFailed.toLocaleString() }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Chart -->
            <Card>
                <CardHeader>
                    <CardTitle>PDF processing over time</CardTitle>
                </CardHeader>
                <CardContent>
                    <PdfStatsChart
                        :labels="stats.labels"
                        :full-labels="stats.fullLabels"
                        :current-index="stats.currentIndex"
                        :success="stats.success"
                        :failed="stats.failed"
                    />
                </CardContent>
            </Card>

            <!-- Table view: the accessible twin of the chart, and where PDF names live -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent PDFs</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="stats.uploads.length === 0" class="text-muted-foreground py-8 text-center text-sm">
                        No PDFs processed in this period yet.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-muted-foreground border-b text-left">
                                    <th class="py-2 pr-4 font-medium">Name</th>
                                    <th class="py-2 pr-4 font-medium">Status</th>
                                    <th class="py-2 pr-4 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(upload, index) in stats.uploads" :key="index" class="border-b last:border-0">
                                    <td class="py-2 pr-4">
                                        <div class="flex items-center gap-2">
                                            <FileText class="text-muted-foreground h-4 w-4 shrink-0" />
                                            <span class="truncate" :title="upload.filename">{{ upload.filename }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2 pr-4">
                                        <Badge
                                            v-if="upload.status === 'success'"
                                            variant="outline"
                                            class="border-transparent bg-[#0ca30c]/10 text-[#0ca30c]"
                                        >
                                            <CheckCircle2 class="mr-1 h-3 w-3" />
                                            Success
                                        </Badge>
                                        <Badge v-else variant="destructive">
                                            <XCircle class="mr-1 h-3 w-3" />
                                            Failed
                                        </Badge>
                                    </td>
                                    <td class="text-muted-foreground py-2 pr-4">{{ formatDate(upload.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
