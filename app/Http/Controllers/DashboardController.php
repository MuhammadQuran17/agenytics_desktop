<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Dashboard\PdfStatsAggregator;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private PdfStatsAggregator $pdfStatsAggregator) {}

    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        $period = $request->query('period', 'day');

        return Inertia::render('Dashboard', [
            'period' => $period,
            'pdfStats' => $this->pdfStatsAggregator->forUser($user->id, $period),
        ]);
    }

    public function pdfStats(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $period = $request->validate([
            'period' => 'required|in:day,week,month,year',
        ])['period'];

        return response()->json($this->pdfStatsAggregator->forUser($user->id, $period));
    }
}
