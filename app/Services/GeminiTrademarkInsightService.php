<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiTrademarkInsightService
{
    private const SCHEMA_VERSION = 'trademark-ai-insights-v2';

    private const ACTIVE_STATUSES = [
        'registered',
        'accepted',
        'accepted & advertised',
        'advertised before accepted',
        'opposed',
        'objected',
        'formalities chk pass',
        'marked for exam',
        'send to vienna codification',
        'exam report issued',
    ];

    private const SYSTEM_INSTRUCTION = <<<'INSTRUCTION'
You are an assistant that explains trademark search risk results.

You will receive a trademark keyword, optional trademark class, optional proposed goods or services, fixed numeric risk results, and a list of matching trademark records.

The numeric values were already calculated by the application. Never recalculate, modify, challenge, or replace the supplied registration probability, conflict risk, risk level, counts, or factor scores.

Your job is only to explain the supplied result in simple and professional English. Base every statement only on the supplied data. Do not invent trademark records, legal outcomes, government decisions, owners, classes, statuses, or similarity values. Do not claim that registration will definitely be approved or rejected.

Do not mention data acquisition, data quality, parsing, source reliability, internal processing, raw data, descriptions removed during cleaning, or system errors. Reasons must explain why the supplied score is high or low. Warnings must be useful and actionable for the user.

A warning is allowed only when the user can take an action, such as selecting a trademark class, entering proposed goods or services, reviewing an active same-class mark, checking a close name variation, or consulting a trademark professional. If a hard conflict is supplied, explain that an active exact same-class mark and overlapping goods or services create a major registration obstacle. Never describe a hard conflict as safe or low risk. If no trademark class was selected, do not provide a numeric conclusion.

The summary must be one or two short sentences. Keep all language short, clear, calm, and easy to understand. Do not provide legal advice. Do not return Markdown, HTML, code fences, or extra JSON properties. Return valid JSON matching the supplied schema only.
INSTRUCTION;

    public function __construct(private readonly TrademarkProbabilityService $probabilityService) {}

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array<int, array<string, mixed>>  $records
     * @return array<string, mixed>
     */
    public function generate(array $analysis, array $records): array
    {
        $relevantMatches = $this->relevantMatches($analysis, $records);

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
                ->post($this->endpoint(), $this->requestPayload($analysis, $relevantMatches));

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

            $validated = $this->applyFixedResultRules($validated, $analysis);

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

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array<int, array<string, mixed>>  $matches
     * @return array<string, mixed>
     */
    private function requestPayload(array $analysis, array $matches): array
    {
        $input = [
            'instruction' => 'Explain the supplied calculated result. Do not change any numeric value. Generate a short summary, evidence-based reasons, and only useful user-actionable warnings.',
            'keyword' => $analysis['keyword'],
            'selected_class' => $analysis['requested_class'],
            'proposed_description' => $analysis['proposed_description'],
            'calculated_result' => [
                'analysis_mode' => $analysis['analysis_mode'],
                'analysis_quality' => $analysis['analysis_quality'],
                'registration_probability' => $analysis['registration_probability'],
                'conflict_risk' => $analysis['conflict_risk'],
                'risk_level' => $analysis['risk_level'],
                'hard_conflict' => $analysis['hard_conflict'],
                'hard_conflict_reason' => $analysis['hard_conflict_reason'],
                'exact_registered_word_marks' => $analysis['exact_registered_word_marks'],
                'exact_registered_device_marks' => $analysis['exact_registered_device_marks'],
                'exact_same_class_word_marks' => $analysis['exact_same_class_word_marks'],
                'exact_same_class_device_marks' => $analysis['exact_same_class_device_marks'],
                'highest_name_similarity' => $analysis['highest_name_similarity'],
                'highest_description_similarity' => $analysis['highest_description_similarity'],
                'similar_active_marks' => $analysis['similar_registered_marks'],
                'same_class_matches' => $analysis['same_class_registered_marks'],
                'active_marks' => $analysis['active_marks'],
                'inactive_marks' => $analysis['inactive_marks'],
            ],
            'risk_factors' => array_map(
                fn (array $factor): array => ['label' => $factor['label'], 'score' => $factor['score']],
                array_values(array_filter($analysis['factors'], fn (array $factor): bool => $factor['score'] !== null)),
            ),
            'relevant_matches' => $matches,
        ];

        return [
            'system_instruction' => ['parts' => [['text' => self::SYSTEM_INSTRUCTION]]],
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => json_encode($input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]],
            ]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->responseSchema(),
            ],
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
                    'type' => 'ARRAY', 'minItems' => 2, 'maxItems' => 5,
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

    /**
     * @param  array<string, mixed>|null  $output
     * @return array<string, mixed>|null
     */
    private function validatedOutput(?array $output): ?array
    {
        if ($output === null || ! $this->hasExactKeys($output, ['summary', 'reasons', 'warnings'])) {
            return null;
        }
        if (! $this->validText($output['summary'], 500) || ! is_array($output['reasons']) || ! array_is_list($output['reasons']) || count($output['reasons']) < 2 || count($output['reasons']) > 5) {
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

        $visibleText = [$output['summary']];
        foreach ($output['reasons'] as $reason) {
            array_push($visibleText, $reason['title'], $reason['detail']);
        }
        foreach ($output['warnings'] as $warning) {
            array_push($visibleText, $warning['title'], $warning['detail'], $warning['action']);
        }
        if ($this->containsInternalLanguage(implode(' ', $visibleText))) {
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

    /**
     * @param  array<string, mixed>  $insights
     * @return array<string, mixed>
     */
    private function applyFixedResultRules(array $insights, array $analysis): array
    {
        if ($analysis['analysis_mode'] === 'preliminary') {
            $insights['summary'] = 'Select the proposed trademark class to calculate a class-specific registration estimate.';
            $insights['warnings'] = $analysis['warnings'];

            return $insights;
        }

        $insights['warnings'] = array_values(array_filter(
            $insights['warnings'],
            fn (array $warning): bool => preg_match('/class.{0,40}(?:not selected|was not selected)|no (?:trademark )?class/iu', implode(' ', $warning)) !== 1,
        ));

        foreach ($analysis['warnings'] as $requiredWarning) {
            $exists = collect($insights['warnings'])->contains(
                fn (array $warning): bool => mb_strtolower($warning['title']) === mb_strtolower($requiredWarning['title'])
            );
            if (! $exists) {
                if (count($insights['warnings']) >= 3) {
                    array_pop($insights['warnings']);
                }
                $insights['warnings'][] = $requiredWarning;
            }
        }

        if ($analysis['hard_conflict']) {
            $insights['summary'] = $analysis['hard_conflict_reason'].' This creates a major registration obstacle and may need professional review.';
            $insights['reasons'] = array_values(array_filter(
                $insights['reasons'],
                fn (array $reason): bool => $reason['impact'] !== 'positive'
                    && preg_match('/\b(?:safe|low risk|no exact (?:word )?mark)\b/iu', $reason['title'].' '.$reason['detail']) !== 1,
            ));
            array_unshift($insights['reasons'], [
                'title' => 'Exact same-class conflict',
                'detail' => $analysis['hard_conflict_reason'],
                'impact' => 'negative',
            ]);
            if (count($insights['reasons']) < 2) {
                $insights['reasons'][] = [
                    'title' => 'Professional review may be needed',
                    'detail' => 'Review the active same-class mark and overlapping goods or services before filing.',
                    'impact' => 'neutral',
                ];
            }
            $insights['reasons'] = array_slice($insights['reasons'], 0, 5);
        }

        return $insights;
    }

    /** @param array<string, mixed> $analysis */
    private function fallback(array $analysis): array
    {
        if ($analysis['analysis_mode'] === 'preliminary') {
            return [
                'generated_by' => 'fallback',
                'summary' => 'Select the proposed trademark class to calculate a class-specific registration estimate.',
                'reasons' => [
                    ['title' => 'General search only', 'detail' => 'The available matches were counted across all trademark classes.', 'impact' => 'neutral'],
                    ['title' => 'Class-specific check pending', 'detail' => 'Same-class trademark conflicts have not been checked.', 'impact' => 'neutral'],
                ],
                'warnings' => $analysis['warnings'],
            ];
        }

        $reasons = [];
        if ($analysis['exact_same_class_word_marks'] > 0) {
            $reasons[] = ['title' => 'Exact same-class Word marks found', 'detail' => 'Active Word marks with the exact name were found in the selected class.', 'impact' => 'negative'];
        } elseif ($analysis['exact_registered_word_marks'] > 0) {
            $reasons[] = ['title' => 'Exact Word marks found', 'detail' => 'Active registered Word marks with the exact name were found.', 'impact' => 'negative'];
        } else {
            $reasons[] = ['title' => 'No exact Word mark found', 'detail' => 'No active registered Word mark with the exact searched name was found.', 'impact' => 'positive'];
        }
        if ($analysis['exact_registered_device_marks'] > 0) {
            $reasons[] = ['title' => 'Device marks found', 'detail' => 'Registered Device marks using the same name were found.', 'impact' => 'negative'];
        }
        if ($analysis['similar_registered_marks'] > 0) {
            $reasons[] = ['title' => 'Similar names found', 'detail' => 'Active trademarks with similar names were found.', 'impact' => 'negative'];
        }
        if (($analysis['same_class_registered_marks'] ?? 0) > 0) {
            $reasons[] = ['title' => 'Selected class has matches', 'detail' => 'Active marks were found in the selected trademark class.', 'impact' => 'negative'];
        }
        if (count($reasons) < 2) {
            $reasons[] = ['title' => 'Overall calculated risk', 'detail' => "The fixed calculation places this search in the {$analysis['risk_level']} risk range.", 'impact' => $analysis['conflict_risk'] < 30 ? 'positive' : 'neutral'];
        }

        $summary = $analysis['hard_conflict']
            ? $analysis['hard_conflict_reason'].' This creates a major registration obstacle and may need professional review.'
            : "The calculated registration chance is {$analysis['registration_probability']}% with a {$analysis['risk_level']} conflict risk.";

        return [
            'generated_by' => 'fallback',
            'summary' => $summary,
            'reasons' => array_slice($reasons, 0, 5),
            'warnings' => $analysis['warnings'],
        ];
    }

    /**
     * @param  array<string, mixed>  $analysis
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private function relevantMatches(array $analysis, array $records): array
    {
        $keyword = $this->normalizeName((string) $analysis['keyword']);
        $selectedClass = $analysis['requested_class'];
        $seen = [];
        $matches = [];

        foreach ($records as $record) {
            $name = $this->plainText((string) ($record['trademark_name'] ?? ''));
            $normalizedName = $this->normalizeName($name);
            $status = $this->normalizeStatus((string) ($record['status'] ?? ''));
            $type = $this->plainText((string) ($record['type'] ?? ''));
            $classes = $this->classes((string) ($record['class'] ?? ''), (string) ($record['description'] ?? ''));
            $proprietor = $this->plainText((string) ($record['proprietor'] ?? ''));
            if ($status !== '' && preg_match('/\s+'.preg_quote($status, '/').'\s*$/iu', $proprietor) === 1) {
                $proprietor = trim((string) preg_replace('/\s+'.preg_quote($status, '/').'\s*$/iu', '', $proprietor));
            }
            $applicationId = trim((string) ($record['application_id'] ?? ''));
            $fingerprint = $applicationId !== ''
                ? 'id:'.mb_strtolower($applicationId)
                : hash('sha256', implode('|', [$normalizedName, implode(',', $classes), mb_strtolower($type), mb_strtolower($proprietor)]));

            if (isset($seen[$fingerprint])) {
                continue;
            }
            $seen[$fingerprint] = true;

            $isActive = in_array($status, self::ACTIVE_STATUSES, true);
            if (! $isActive) {
                continue;
            }
            $isExact = $normalizedName === $keyword;
            $similarity = $this->probabilityService->nameSimilarity($keyword, $normalizedName);
            $isSameClass = $selectedClass !== null && in_array((string) $selectedClass, $classes, true);
            $typeKey = mb_strtolower($type);
            $rank = match (true) {
                $isExact && $isActive && in_array($typeKey, ['word', 'wordmark', 'word mark'], true) => 1,
                $isExact && $isActive && in_array($typeKey, ['device', 'logo', 'label', 'device mark'], true) => 2,
                $isSameClass && $isActive => 3,
                $similarity >= 80 && $isActive => 4,
                default => 5,
            };
            $description = strip_tags(html_entity_decode((string) ($record['description'] ?? ''), ENT_QUOTES | ENT_HTML5));
            if (preg_match('/View All Results|Search by Proprietor Name|Upgrade\s*₹/iu', $description) === 1) {
                $description = '';
            }

            $matches[] = [
                '_rank' => $rank,
                '_similarity' => $similarity,
                'trademark_name' => $name,
                'status' => $this->plainText((string) ($record['status'] ?? '')),
                'class' => $this->plainText((string) ($record['class'] ?? '')),
                'type' => $type,
                'proprietor' => $proprietor,
                'calculated_name_similarity' => $similarity,
                'description' => mb_substr(trim(preg_replace('/\s+/u', ' ', $description) ?? ''), 0, 300),
            ];
        }

        usort($matches, fn (array $left, array $right): int => [$left['_rank'], -$left['_similarity']] <=> [$right['_rank'], -$right['_similarity']]);

        return array_map(function (array $match): array {
            unset($match['_rank'], $match['_similarity']);

            return $match;
        }, array_slice($matches, 0, 10));
    }

    /** @param array<string, mixed> $analysis */
    private function cacheKey(array $analysis, array $records): string
    {
        $fingerprints = array_map(fn (array $record): string => implode('|', [
            trim((string) ($record['application_id'] ?? '')),
            $this->normalizeName((string) ($record['trademark_name'] ?? '')),
            $this->normalizeStatus((string) ($record['status'] ?? '')),
            trim((string) ($record['class'] ?? '')),
            mb_strtolower(trim((string) ($record['type'] ?? ''))),
        ]), $records);
        sort($fingerprints, SORT_STRING);

        return 'trademark-ai-insights:'.hash('sha256', implode('::', [
            self::SCHEMA_VERSION,
            (string) config('services.gemini.model'),
            $this->normalizeName((string) $analysis['keyword']),
            (string) ($analysis['requested_class'] ?? ''),
            $this->normalizeName((string) ($analysis['proposed_description'] ?? '')),
            (string) $analysis['hard_conflict'],
            (string) $analysis['registration_probability'],
            (string) $analysis['conflict_risk'],
            (string) $analysis['risk_level'],
            implode(';;', $fingerprints),
        ]));
    }

    private function endpoint(): string
    {
        $model = rawurlencode((string) config('services.gemini.model', 'gemini-2.5-flash'));

        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /** @param array<string, mixed> $value */
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

    private function containsInternalLanguage(string $text): bool
    {
        return preg_match('/scrap(?:e|ed|ing)|malformed\s+(?:scraped\s+)?data|incomplete\s+(?:scraped\s+)?data|source website|parsing issue|quickcompany|contaminated description|internal data|raw data|internal processing|processing error/iu', $text) === 1;
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(str_replace(['™', '®'], '', trim($value)));
        $value = preg_replace('/[^\pL\pN\s]/u', '', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function normalizeStatus(string $status): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $status) ?? ''));
    }

    private function plainText(string $value): string
    {
        $value = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /** @return array<int, string> */
    private function classes(string $classField, string $description): array
    {
        $classes = preg_split('/\s*,\s*/', $classField, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        preg_match_all('/\[\s*class\s*:\s*([^\]]+)\]/iu', $description, $matches);
        foreach ($matches[1] ?? [] as $match) {
            array_push($classes, ...(preg_split('/\s*,\s*/', $match, -1, PREG_SPLIT_NO_EMPTY) ?: []));
        }

        $classes = array_values(array_unique(array_map(fn (string $class): string => $this->plainText($class), $classes)));
        sort($classes, SORT_NATURAL);

        return $classes;
    }
}
