<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\PdfUpload;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PdfStatsAggregator
{
    /**
     * @var array<string, array{unit: string, count: int}>
     */
    private const PERIODS = [
        'day' => ['unit' => 'day', 'count' => 14],
        'week' => ['unit' => 'week', 'count' => 12],
        'month' => ['unit' => 'month', 'count' => 12],
        'year' => ['unit' => 'year', 'count' => 5],
    ];

    /**
     * @return array{labels: array<int, string>, success: array<int, int>, failed: array<int, int>, uploads: Collection}
     */
    public function forUser(string $userId, string $period): array
    {
        $config = self::PERIODS[$period] ?? self::PERIODS['day'];

        $buckets = $this->buildBuckets($config['unit'], $config['count']);

        $uploads = PdfUpload::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $buckets->first()['start'])
            ->orderByDesc('created_at')
            ->get(['filename', 'status', 'created_at']);

        $buckets = $buckets->map(function (array $bucket) use ($uploads) {
            $inBucket = $uploads->whereBetween('created_at', [$bucket['start'], $bucket['end']]);

            return [
                ...$bucket,
                'success' => $inBucket->where('status', 'success')->count(),
                'failed' => $inBucket->where('status', 'failed')->count(),
            ];
        });

        return [
            'labels' => $buckets->pluck('label')->all(),
            'success' => $buckets->pluck('success')->all(),
            'failed' => $buckets->pluck('failed')->all(),
            'uploads' => $uploads->map(fn (PdfUpload $upload) => [
                'filename' => $upload->filename,
                'status' => $upload->status,
                'created_at' => $upload->created_at->toIso8601String(),
            ])->values(),
        ];
    }

    /**
     * @return Collection<int, array{start: Carbon, end: Carbon, label: string}>
     */
    private function buildBuckets(string $unit, int $count): Collection
    {
        return collect(range($count - 1, 0))->map(function (int $offset) use ($unit) {
            $start = match ($unit) {
                'day' => now()->subDays($offset)->startOfDay(),
                'week' => now()->subWeeks($offset)->startOfWeek(),
                'month' => now()->subMonths($offset)->startOfMonth(),
                'year' => now()->subYears($offset)->startOfYear(),
            };

            $end = match ($unit) {
                'day' => $start->copy()->endOfDay(),
                'week' => $start->copy()->endOfWeek(),
                'month' => $start->copy()->endOfMonth(),
                'year' => $start->copy()->endOfYear(),
            };

            $label = match ($unit) {
                'day' => $start->format('M j'),
                'week' => $start->format('M j'),
                'month' => $start->format('M Y'),
                'year' => $start->format('Y'),
            };

            return ['start' => $start, 'end' => $end, 'label' => $label];
        });
    }
}
