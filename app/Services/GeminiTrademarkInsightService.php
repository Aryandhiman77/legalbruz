<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiTrademarkInsightService
{
    public const SCHEMA_VERSION = 'trademark-ai-insights-v4';

    private const SYSTEM_INSTRUCTION = <<<'INSTRUCTION'
You explain a trademark search-data risk calculation.

The numeric score and all counts have already been calculated by Laravel. Explain the supplied result only. Never change or challenge the supplied numeric values.

Never calculate or return a registration percentage, conflict percentage, risk level, count, or chart value. Never invent trademark records, statuses, owners, classes, legal exceptions, or registry outcomes. Never claim acceptance or refusal is guaranteed. Never mention scraping, malformed data, parsing, internal processing, raw data, or API errors. Never ask the user to choose a trademark class or enter goods or services.

Use only the supplied calculated result and relevant records. Return a short professional summary, evidence-based reasons, and useful warnings with recommended actions. A zero-result search is positive search evidence but does not prove legal availability. Return valid JSON matching the supplied schema only, without Markdown or HTML.
INSTRUCTION;

    public function __construct(private readonly TrademarkProbabilityService $probabilityService) {}

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array<int, array<string, mixed>>  $records
     * @return array<string, mixed>
     */
    public function generate(array $analysis, array $records): array
    {
        if (! config('services.gemini.enabled') || blank(config('services.gemini.api_key'))) {
            return $this->fallback($analysis);
        }
        $cacheKey = $this->cacheKey($analysis, $records);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withHeaders(['x-goog-api-key' => (string) config('services.gemini.api_key')])
                ->connectTimeout(5)
                ->timeout(15)
                ->retry(2, 250, throw: false)
                ->post($this->endpoint(), $this->requestPayload($analysis, $this->relevantMatches($analysis, $records)));
            if (! $response->successful()) {
                Log::warning('Gemini trademark insights request failed.', ['status' => $response->status()]);

                return $this->fallback($analysis);
            }
            $text = $response->json('candidates.0.content.parts.0.text');
            $decoded = is_string($text) ? json_decode($text, true) : null;
            $validated = $this->validatedOutput($decoded);
            if ($validated === null) {
                Log::warning('Gemini trademark insights returned an invalid structured response.');

                return $this->fallback($analysis);
            }
            $validated = $this->applyRequiredWarnings($validated, $analysis);
            $insights = ['generated_by' => 'gemini', ...$validated];
            Cache::put($cacheKey, $insights, now()->addMinutes(30));

            return $insights;
        } catch (ConnectionException $exception) {
            Log::warning('Gemini trademark insights connection failed.', ['exception' => $exception::class]);
        } catch (Throwable $exception) {
            Log::warning('Gemini trademark insights generation failed.', ['exception' => $exception::class]);
        }

        return $this->fallback($analysis);
    }

    /** @param array<int, array<string, mixed>> $matches */
    private function requestPayload(array $analysis, array $matches): array
    {
        $countKeys = [
            'total_unique_marks', 'exact_active_word_marks', 'exact_active_device_marks',
            'exact_pending_word_marks', 'exact_pending_device_marks', 'very_close_active_marks',
            'close_active_marks', 'phonetic_active_matches', 'inactive_exact_marks',
            'active_marks', 'pending_marks', 'inactive_marks', 'unique_active_classes',
            'highest_name_similarity',
        ];
        $counts = [];
        foreach ($countKeys as $key) {
            $counts[$key] = $analysis[$key];
        }
        $input = [
            'instruction' => 'Explain the supplied fixed Laravel result. Do not calculate or output numeric scores.',
            'keyword' => $analysis['keyword'],
            'calculated_result' => [
                'registration_probability' => $analysis['registration_probability'],
                'conflict_risk' => $analysis['conflict_risk'],
                'risk_level' => $analysis['risk_level'],
                'confidence_score' => $analysis['confidence_score'],
                'counts' => $counts,
                'factors' => $analysis['factors'],
            ],
            'relevant_records' => $matches,
        ];

        return [
            'system_instruction' => ['parts' => [['text' => self::SYSTEM_INSTRUCTION]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => json_encode($input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]]]],
            'generationConfig' => ['responseMimeType' => 'application/json', 'responseSchema' => $this->responseSchema()],
        ];
    }

    /** @return array<string, mixed> */
    private function responseSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'required' => ['summary', 'reasons', 'warnings'],
            'propertyOrdering' => ['summary', 'reasons', 'warnings'],
            'properties' => [
                'summary' => ['type' => 'STRING', 'maxLength' => 500],
                'reasons' => [
                    'type' => 'ARRAY', 'minItems' => 1, 'maxItems' => 5,
                    'items' => [
                        'type' => 'OBJECT',
                        'required' => ['title', 'detail', 'impact'],
                        'properties' => [
                            'title' => ['type' => 'STRING', 'maxLength' => 120],
                            'detail' => ['type' => 'STRING', 'maxLength' => 500],
                            'impact' => ['type' => 'STRING', 'enum' => ['positive', 'negative', 'neutral']],
                        ],
                    ],
                ],
                'warnings' => [
                    'type' => 'ARRAY', 'maxItems' => 3,
                    'items' => [
                        'type' => 'OBJECT',
                        'required' => ['title', 'detail', 'action'],
                        'properties' => [
                            'title' => ['type' => 'STRING', 'maxLength' => 120],
                            'detail' => ['type' => 'STRING', 'maxLength' => 500],
                            'action' => ['type' => 'STRING', 'maxLength' => 300],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    private function validatedOutput(mixed $output): ?array
    {
        if (! is_array($output) || ! $this->hasExactKeys($output, ['summary', 'reasons', 'warnings'])) {
            return null;
        }
        if (! $this->validText($output['summary'], 500) || ! is_array($output['reasons']) || ! array_is_list($output['reasons']) || count($output['reasons']) < 1 || count($output['reasons']) > 5) {
            return null;
        }
        if (! is_array($output['warnings']) || ! array_is_list($output['warnings']) || count($output['warnings']) > 3) {
            return null;
        }
        foreach ($output['reasons'] as $reason) {
            if (! is_array($reason) || ! $this->hasExactKeys($reason, ['title', 'detail', 'impact']) || ! $this->validText($reason['title'], 120) || ! $this->validText($reason['detail'], 500) || ! in_array($reason['impact'], ['positive', 'negative', 'neutral'], true)) {
                return null;
            }
        }
        foreach ($output['warnings'] as $warning) {
            if (! is_array($warning) || ! $this->hasExactKeys($warning, ['title', 'detail', 'action']) || ! $this->validText($warning['title'], 120) || ! $this->validText($warning['detail'], 500) || ! $this->validText($warning['action'], 300)) {
                return null;
            }
        }
        $visible = [$output['summary']];
        foreach ($output['reasons'] as $reason) {
            array_push($visible, $reason['title'], $reason['detail']);
        }
        foreach ($output['warnings'] as $warning) {
            array_push($visible, $warning['title'], $warning['detail'], $warning['action']);
        }
        if ($this->containsForbiddenLanguage(implode(' ', $visible))) {
            return null;
        }

        return [
            'summary' => trim($output['summary']),
            'reasons' => array_map(fn (array $reason): array => [
                'title' => trim($reason['title']),
                'detail' => trim($reason['detail']),
                'impact' => $reason['impact'],
            ], $output['reasons']),
            'warnings' => array_map(fn (array $warning): array => [
                'title' => trim($warning['title']),
                'detail' => trim($warning['detail']),
                'action' => trim($warning['action']),
            ], $output['warnings']),
        ];
    }

    private function applyRequiredWarnings(array $insights, array $analysis): array
    {
        if ($analysis['total_unique_marks'] === 0) {
            $insights['warnings'] = $analysis['warnings'];
        }

        return $insights;
    }

    /** @return array<string, mixed> */
    private function fallback(array $analysis): array
    {
        $reasons = [];
        if ($analysis['total_unique_marks'] === 0) {
            $summary = 'The available search data shows no active exact or close trademark match.';
            $reasons[] = ['title' => 'No matching record found', 'detail' => 'No matching active mark was found in the available search records.', 'impact' => 'positive'];
        } else {
            $summary = "The fixed search-data calculation gives a {$analysis['registration_probability']}% registration estimate and {$analysis['risk_level']} risk.";
        }
        if ($analysis['exact_active_word_marks'] > 0) {
            $reasons[] = ['title' => 'Exact active Word marks', 'detail' => 'Active Word trademarks with the exact searched name were found.', 'impact' => 'negative'];
        }
        if ($analysis['exact_active_device_marks'] > 0) {
            $reasons[] = ['title' => 'Exact active Device marks', 'detail' => 'Active Device trademarks using the exact searched name were found.', 'impact' => 'negative'];
        }
        if ($analysis['similar_active_marks'] > 0) {
            $reasons[] = ['title' => 'Similar active names', 'detail' => 'Active trademarks with similar names were found.', 'impact' => 'negative'];
        }
        if ($reasons === []) {
            $reasons[] = ['title' => 'Search record context', 'detail' => 'The supplied records were evaluated by name similarity, status, type and class spread.', 'impact' => 'neutral'];
        }

        return [
            'generated_by' => 'fallback',
            'summary' => $summary,
            'reasons' => array_slice($reasons, 0, 5),
            'warnings' => $analysis['warnings'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function relevantMatches(array $analysis, array $records): array
    {
        $seen = [];
        $matches = [];
        foreach ($records as $record) {
            $applicationId = trim((string) ($record['application_id'] ?? ''));
            $name = $this->plainText((string) ($record['trademark_name'] ?? ''));
            $status = $this->plainText((string) ($record['status'] ?? ''));
            $type = $this->plainText((string) ($record['type'] ?? ''));
            $class = $this->plainText((string) ($record['class'] ?? ''));
            $proprietor = $this->plainText((string) ($record['proprietor'] ?? ''));
            $fingerprint = $applicationId !== '' ? 'id:'.mb_strtolower($applicationId) : hash('sha256', mb_strtolower(implode('|', [$name, $status, $type, $class, $proprietor])));
            if (isset($seen[$fingerprint])) {
                continue;
            }
            $seen[$fingerprint] = true;
            $similarity = $this->probabilityService->nameSimilarity((string) $analysis['keyword'], $name);
            $matches[] = [
                '_similarity' => $similarity,
                'trademark_name' => $name,
                'name_similarity' => (int) round($similarity),
                'status' => $status,
                'type' => $type,
                'class' => $class,
                'proprietor' => $proprietor,
            ];
        }
        usort($matches, fn (array $left, array $right): int => $right['_similarity'] <=> $left['_similarity']);

        return array_map(function (array $match): array {
            unset($match['_similarity']);

            return $match;
        }, array_slice($matches, 0, 10));
    }

    private function cacheKey(array $analysis, array $records): string
    {
        $fingerprints = array_map(fn (array $record): string => implode('|', [
            trim((string) ($record['application_id'] ?? '')),
            mb_strtolower(trim((string) ($record['trademark_name'] ?? ''))),
            mb_strtolower(trim((string) ($record['status'] ?? ''))),
            mb_strtolower(trim((string) ($record['type'] ?? ''))),
            trim((string) ($record['class'] ?? '')),
        ]), $records);
        sort($fingerprints, SORT_STRING);

        return 'trademark-ai-insights:'.hash('sha256', implode('::', [
            self::SCHEMA_VERSION,
            (string) config('services.gemini.model'),
            mb_strtolower(trim((string) $analysis['keyword'])),
            (string) $analysis['registration_probability'],
            (string) $analysis['conflict_risk'],
            (string) $analysis['risk_level'],
            (string) $analysis['confidence_score'],
            json_encode($analysis['factors']),
            $fingerprints === [] ? 'empty-result-set' : implode(';;', $fingerprints),
        ]));
    }

    private function endpoint(): string
    {
        $model = rawurlencode((string) config('services.gemini.model', 'gemini-2.5-flash'));

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    private function hasExactKeys(array $value, array $keys): bool
    {
        $actual = array_keys($value);
        sort($actual);
        sort($keys);

        return $actual === $keys;
    }

    private function validText(mixed $value, int $maximum): bool
    {
        return is_string($value)
            && trim($value) !== ''
            && mb_strlen($value) <= $maximum
            && strip_tags($value) === $value
            && preg_match('/```|(?:^|\n)\s*#{1,6}\s|\*\*|__|\[[^\]]+\]\([^)]+\)/u', $value) !== 1;
    }

    private function containsForbiddenLanguage(string $text): bool
    {
        return preg_match('/scrap(?:e|ed|ing)|malformed|source website|parsing|internal (?:data|processing)|raw data|api (?:key|error)|(?:select|choose).{0,30}(?:trademark )?class|class.{0,30}(?:not selected|required)/iu', $text) === 1;
    }

    private function plainText(string $value): string
    {
        $value = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }
}
