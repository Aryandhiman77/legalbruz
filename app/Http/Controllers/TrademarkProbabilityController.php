<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrademarkProbabilityRequest;
use App\Services\GeminiTrademarkInsightService;
use App\Services\TrademarkProbabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TrademarkProbabilityController extends Controller
{
    public function __construct(
        private readonly TrademarkProbabilityService $probabilityService,
        private readonly GeminiTrademarkInsightService $insightService,
    ) {}

    public function __invoke(TrademarkProbabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cacheKey = $this->probabilityService->cacheKey(
            $validated['keyword'],
            $validated['class'] ?? null,
            $validated['data'],
            $validated['proposed_description'] ?? null,
        );

        $analysis = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn (): array => $this->probabilityService->analyze(
                $validated['keyword'],
                $validated['data'],
                $validated['class'] ?? null,
                $validated['proposed_description'] ?? null,
            ),
        );
        $analysis['ai_insights'] = $this->insightService->generate($analysis, $validated['data']);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
        ]);
    }
}
