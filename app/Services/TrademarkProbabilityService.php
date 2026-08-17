<?php

namespace App\Services;

class TrademarkProbabilityService
{
    private const SCORING_SCHEMA_VERSION = 'trademark-probability-v4';

    private const STRONG_STATUSES = ['registered', 'accepted', 'accepted & advertised', 'advertised before accepted'];

    private const PENDING_STATUSES = [
        'opposed', 'objected', 'formalities chk pass', 'marked for exam', 'exam report issued',
        'send to vienna codification', 'pending',
    ];

    private const INACTIVE_STATUSES = ['refused', 'abandoned', 'removed', 'withdrawn', 'cancelled', 'expired'];

    public const DISCLAIMER = 'This report is intended solely as an indicative conflict-risk assessment based on available trademark search records. It should not be treated as an accurate, exhaustive, or conclusive legal opinion, and it does not guarantee acceptance, refusal, or any specific outcome before the Trademark Registry.';

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function analyze(string $keyword, array $records, array $options = []): array
    {
        $keywordForms = $this->nameForms($keyword);
        ['records' => $cleanRecords] = $this->cleanRecords($records);
        $counts = [
            'total_unique_marks' => count($cleanRecords),
            'exact_active_word_marks' => 0,
            'exact_active_device_marks' => 0,
            'exact_pending_word_marks' => 0,
            'exact_pending_device_marks' => 0,
            'very_close_active_marks' => 0,
            'close_active_marks' => 0,
            'phonetic_active_matches' => 0,
            'inactive_exact_marks' => 0,
            'active_marks' => 0,
            'pending_marks' => 0,
            'inactive_marks' => 0,
            'unique_active_classes' => 0,
            'highest_name_similarity' => 0,
        ];
        $activeClasses = [];
        $highestSimilarActive = 0.0;
        $completeRecords = 0;

        foreach ($cleanRecords as &$record) {
            $isStrong = in_array($record['normalized_status'], self::STRONG_STATUSES, true);
            $isPending = in_array($record['normalized_status'], self::PENDING_STATUSES, true);
            $isInactive = in_array($record['normalized_status'], self::INACTIVE_STATUSES, true);
            $isExact = $record['compact_normalized_name'] !== ''
                && $record['compact_normalized_name'] === $keywordForms['compact'];
            $similarity = $this->nameSimilarity($keyword, $record['spaced_normalized_name']);
            $phonetic = ! $isExact && $this->phoneticMatch($keyword, $record['spaced_normalized_name']);
            $record['name_similarity'] = (int) round($similarity);
            $record['phonetic_match'] = $phonetic;

            if ($record['spaced_normalized_name'] !== '' && $record['normalized_status'] !== '' && $record['normalized_type'] !== 'Other') {
                $completeRecords++;
            }
            if ($isStrong) {
                $counts['active_marks']++;
                array_push($activeClasses, ...$record['classes']);
                $counts['highest_name_similarity'] = max($counts['highest_name_similarity'], (int) round($similarity));
                if ($isExact && $record['normalized_type'] === 'Word') {
                    $counts['exact_active_word_marks']++;
                } elseif ($isExact && $record['normalized_type'] === 'Device') {
                    $counts['exact_active_device_marks']++;
                } elseif ($similarity >= 90) {
                    $counts['very_close_active_marks']++;
                    $highestSimilarActive = max($highestSimilarActive, $similarity);
                } elseif ($similarity >= 80) {
                    $counts['close_active_marks']++;
                    $highestSimilarActive = max($highestSimilarActive, $similarity);
                } elseif ($similarity >= 70) {
                    $highestSimilarActive = max($highestSimilarActive, $similarity);
                }
                if ($phonetic) {
                    $counts['phonetic_active_matches']++;
                }
            } elseif ($isPending) {
                $counts['pending_marks']++;
                $counts['highest_name_similarity'] = max($counts['highest_name_similarity'], (int) round($similarity));
                if ($isExact && $record['normalized_type'] === 'Word') {
                    $counts['exact_pending_word_marks']++;
                } elseif ($isExact && $record['normalized_type'] === 'Device') {
                    $counts['exact_pending_device_marks']++;
                }
            } elseif ($isInactive) {
                $counts['inactive_marks']++;
                if ($isExact) {
                    $counts['inactive_exact_marks']++;
                }
            }
        }
        unset($record);

        $counts['unique_active_classes'] = count(array_unique($activeClasses));
        $similarActiveMarks = $counts['very_close_active_marks'] + $counts['close_active_marks'];
        $factors = $this->factorScores($counts, $highestSimilarActive);
        $conflictRisk = $this->conflictRisk($counts, $factors);
        $registrationProbability = 100 - $conflictRisk;
        $confidenceScore = $this->confidenceScore($cleanRecords, $completeRecords, $options);

        return [
            'analysis_mode' => 'search_data',
            'analysis_quality' => $confidenceScore >= 80 ? 'high' : ($confidenceScore >= 70 ? 'standard' : 'limited'),
            'keyword' => trim($keyword),
            'registration_probability' => $registrationProbability,
            'conflict_risk' => $conflictRisk,
            'risk_level' => $this->riskLevel($conflictRisk),
            'confidence_score' => $confidenceScore,
            ...$counts,
            'exact_registered_word_marks' => $counts['exact_active_word_marks'],
            'exact_registered_device_marks' => $counts['exact_active_device_marks'],
            'similar_active_marks' => $similarActiveMarks,
            'similar_registered_marks' => $similarActiveMarks,
            'factors' => $factors,
            'reasons' => $this->deterministicReasons($counts),
            'warnings' => $this->deterministicWarnings($counts),
            'disclaimer' => self::DISCLAIMER,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array{records: array<int, array<string, mixed>>, warnings: array<int, string>}
     */
    public function cleanRecords(array $records): array
    {
        $clean = [];
        $seen = [];
        foreach ($records as $record) {
            $forms = $this->nameForms((string) ($record['trademark_name'] ?? ''));
            $status = $this->normalizeStatus((string) ($record['status'] ?? ''));
            $type = $this->normalizeType((string) ($record['type'] ?? ''));
            $description = mb_substr($this->plainText((string) ($record['description'] ?? '')), 0, 2000);
            if ($this->isContaminatedDescription($description)) {
                $description = '';
            }
            $classes = $this->parseClasses((string) ($record['class'] ?? ''), $description);
            $applicationId = trim((string) ($record['application_id'] ?? ''));
            $proprietor = $this->plainText((string) ($record['proprietor'] ?? ''));
            if ($status !== '' && preg_match('/\s+'.preg_quote($status, '/').'\s*$/iu', $proprietor) === 1) {
                $proprietor = trim((string) preg_replace('/\s+'.preg_quote($status, '/').'\s*$/iu', '', $proprietor));
            }
            $dedupeKey = $applicationId !== ''
                ? 'id:'.mb_strtolower($applicationId)
                : 'record:'.hash('sha256', implode('|', [$forms['compact'], $status, $type, implode(',', $classes), mb_strtolower($proprietor)]));
            if (isset($seen[$dedupeKey])) {
                continue;
            }
            $seen[$dedupeKey] = true;
            $clean[] = [
                'application_id' => $applicationId,
                'spaced_normalized_name' => $forms['spaced'],
                'compact_normalized_name' => $forms['compact'],
                'normalized_status' => $status,
                'normalized_type' => $type,
                'classes' => $classes,
                'proprietor' => $proprietor,
                'description' => $description,
            ];
        }

        return ['records' => $clean, 'warnings' => []];
    }

    public function nameSimilarity(string $first, string $second): float
    {
        $firstForms = $this->nameForms($first);
        $secondForms = $this->nameForms($second);
        if ($firstForms['compact'] === $secondForms['compact']) {
            return $firstForms['compact'] === '' ? 0.0 : 100.0;
        }
        $scores = [];
        foreach ([[$firstForms['spaced'], $secondForms['spaced']], [$firstForms['compact'], $secondForms['compact']]] as [$left, $right]) {
            $maximum = max(strlen($left), strlen($right));
            if ($maximum === 0) {
                continue;
            }
            $scores[] = (1 - (levenshtein($left, $right) / $maximum)) * 100;
            similar_text($left, $right, $similarText);
            $scores[] = $similarText;
            $scores[] = $this->prefixSuffixSimilarity($left, $right);
        }
        if ($this->phoneticMatch($first, $second)) {
            $scores[] = 82;
        }

        return round(max(0, min(100, ...$scores)), 2);
    }

    public function phoneticMatch(string $first, string $second): bool
    {
        $first = $this->nameForms($first)['compact'];
        $second = $this->nameForms($second)['compact'];
        if (preg_match('/[a-z]/i', $first) !== 1 || preg_match('/[a-z]/i', $second) !== 1) {
            return false;
        }

        return metaphone($first) !== '' && metaphone($first) === metaphone($second);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @param  array<string, mixed>  $options
     */
    public function cacheKey(string $keyword, array $records, array $options = []): string
    {
        ['records' => $cleanRecords] = $this->cleanRecords($records);
        $parts = array_map(fn (array $record): string => implode('|', [
            $record['application_id'], $record['compact_normalized_name'], $record['normalized_status'],
            $record['normalized_type'], implode(',', $record['classes']),
        ]), $cleanRecords);
        sort($parts, SORT_STRING);

        return self::SCORING_SCHEMA_VERSION.':'.hash('sha256', implode('::', [
            $this->nameForms($keyword)['compact'],
            $parts === [] ? 'empty-result-set' : implode(';;', $parts),
            (string) ($options['gemini_model'] ?? ''),
            (string) ($options['insight_schema_version'] ?? ''),
        ]));
    }

    /** @param array<string, int> $counts */
    private function factorScores(array $counts, float $highestSimilarActive): array
    {
        $exactName = match (true) {
            $counts['exact_active_word_marks'] >= 2 => min(100, 95 + (($counts['exact_active_word_marks'] - 2) * 2)),
            $counts['exact_active_word_marks'] === 1 => 85,
            $counts['exact_active_device_marks'] >= 3 => 80,
            $counts['exact_active_device_marks'] === 2 => 65,
            $counts['exact_active_device_marks'] === 1 => 50,
            default => 0,
        };
        $registered = match (true) {
            $counts['active_marks'] >= 8 => min(100, 90 + ($counts['active_marks'] - 8) * 2),
            $counts['active_marks'] >= 4 => 70,
            $counts['active_marks'] >= 2 => 50,
            $counts['active_marks'] === 1 => 30,
            default => 0,
        };
        $similar = match (true) {
            $highestSimilarActive >= 90 => min(100, 85 + (($counts['very_close_active_marks'] - 1) * 5)),
            $highestSimilarActive >= 80 => 65,
            $highestSimilarActive >= 70 => 40,
            default => 0,
        };
        $phonetic = match (true) {
            $counts['phonetic_active_matches'] >= 2 => min(100, 60 + ($counts['phonetic_active_matches'] * 10)),
            $counts['phonetic_active_matches'] === 1 => 50,
            default => 0,
        };
        $classSpread = match (true) {
            $counts['unique_active_classes'] >= 7 => min(100, 85 + (($counts['unique_active_classes'] - 7) * 3)),
            $counts['unique_active_classes'] >= 4 => 65,
            $counts['unique_active_classes'] >= 2 => 40,
            $counts['unique_active_classes'] === 1 => 20,
            default => 0,
        };
        $statusRisk = min(100, ($counts['active_marks'] * 12) + ($counts['pending_marks'] * 7));

        return [
            ['key' => 'exact_name', 'label' => 'Exact Name Conflict', 'score' => $exactName],
            ['key' => 'registered_marks', 'label' => 'Registered Marks', 'score' => $registered],
            ['key' => 'similar_names', 'label' => 'Similar Names', 'score' => $similar],
            ['key' => 'phonetic_match', 'label' => 'Phonetic Match', 'score' => $phonetic],
            ['key' => 'class_spread', 'label' => 'Class Spread', 'score' => $classSpread],
            ['key' => 'status_risk', 'label' => 'Status Risk', 'score' => $statusRisk],
        ];
    }

    /** @param array<string, int> $counts */
    private function conflictRisk(array $counts, array $factors): int
    {
        if ($counts['total_unique_marks'] === 0) {
            return 5;
        }
        $scores = collect($factors)->pluck('score', 'key');
        $weighted = (int) round(
            ($scores['exact_name'] * 0.35)
            + ($scores['registered_marks'] * 0.20)
            + ($scores['similar_names'] * 0.20)
            + ($scores['phonetic_match'] * 0.10)
            + ($scores['class_spread'] * 0.05)
            + ($scores['status_risk'] * 0.10)
        );
        $floor = 5;
        if ($counts['exact_active_word_marks'] >= 5) {
            $floor = 97;
        } elseif ($counts['exact_active_word_marks'] >= 2) {
            $floor = 92;
        } elseif ($counts['exact_active_word_marks'] === 1) {
            $floor = 82;
        } elseif ($counts['exact_active_device_marks'] >= 3) {
            $floor = 65;
        } elseif ($counts['exact_active_device_marks'] === 2) {
            $floor = 55;
        } elseif ($counts['exact_active_device_marks'] === 1) {
            $floor = 40;
        }
        if ($counts['very_close_active_marks'] > 0) {
            $floor = max($floor, $counts['very_close_active_marks'] > 1 ? 80 : 65);
        }
        if ($counts['close_active_marks'] > 0) {
            $floor = max($floor, min(74, 45 + (($counts['close_active_marks'] - 1) * 8)));
        }
        if ($counts['phonetic_active_matches'] > 0) {
            $floor = max($floor, min(85, 55 + (($counts['phonetic_active_matches'] - 1) * 10)));
        }
        if ($counts['exact_active_word_marks'] === 0 && $counts['exact_pending_word_marks'] > 0) {
            $floor = max($floor, 60);
        }
        $hasActiveSimilar = $counts['exact_active_word_marks'] > 0
            || $counts['exact_active_device_marks'] > 0
            || $counts['very_close_active_marks'] > 0
            || $counts['close_active_marks'] > 0
            || $counts['phonetic_active_matches'] > 0;
        if (! $hasActiveSimilar && $counts['exact_pending_word_marks'] === 0) {
            $contextRisk = 8 + min(12, ($counts['pending_marks'] * 3) + $counts['inactive_marks']);
            $floor = max($floor, $contextRisk);
        }

        return max(5, min(97, max($weighted, $floor)));
    }

    /** @param array<int, array<string, mixed>> $records */
    private function confidenceScore(array $records, int $completeRecords, array $options): int
    {
        $sourceType = $options['source_type'] ?? 'third_party';
        if ($records === []) {
            return $sourceType === 'official' ? 90 : ($sourceType === 'official_and_third_party' ? 92 : 75);
        }
        $completeness = $completeRecords / count($records);
        if ($sourceType === 'official' || $sourceType === 'official_and_third_party') {
            return min(95, 85 + (int) round($completeness * 10));
        }

        return min(80, 70 + (int) round($completeness * 10));
    }

    /** @param array<string, int> $counts */
    private function deterministicReasons(array $counts): array
    {
        $reasons = [];
        if ($counts['total_unique_marks'] === 0) {
            $reasons[] = 'No matching active mark was found in the available search records.';
        }
        if ($counts['exact_active_word_marks'] > 0) {
            $reasons[] = 'Active Word trademarks with the exact searched name were found.';
        }
        if ($counts['exact_active_device_marks'] > 0) {
            $reasons[] = 'Active Device trademarks using the exact searched name were found.';
        }
        if (($counts['very_close_active_marks'] + $counts['close_active_marks']) > 0) {
            $reasons[] = 'Active trademarks with similar names were found.';
        }

        return $reasons;
    }

    /** @param array<string, int> $counts */
    private function deterministicWarnings(array $counts): array
    {
        if ($counts['total_unique_marks'] !== 0) {
            return [];
        }

        return [[
            'title' => 'Search estimate only',
            'detail' => 'A zero-result search does not confirm final legal availability or approval.',
            'action' => 'Review the full report and consider a professional trademark check before filing.',
        ]];
    }

    private function riskLevel(int $risk): string
    {
        return match (true) {
            $risk <= 24 => 'Low',
            $risk <= 49 => 'Moderate',
            $risk <= 74 => 'High',
            $risk <= 89 => 'Very High',
            default => 'Critical',
        };
    }

    /** @return array{spaced: string, compact: string} */
    private function nameForms(string $value): array
    {
        $value = mb_strtolower(str_replace(['™', '®'], '', trim($value)));
        if (function_exists('transliterator_transliterate')) {
            $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value) ?: $value;
        }
        $spaced = preg_replace('/[^\pL\pN\s]/u', ' ', $value) ?? '';
        $spaced = trim(preg_replace('/\s+/u', ' ', $spaced) ?? '');

        return ['spaced' => $spaced, 'compact' => str_replace(' ', '', $spaced)];
    }

    private function normalizeStatus(string $status): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $status) ?? ''));
    }

    private function normalizeType(string $type): string
    {
        return match (mb_strtolower(trim($type))) {
            'word', 'wordmark', 'word mark' => 'Word',
            'device', 'logo', 'label', 'device mark', 'combined', 'composite' => 'Device',
            default => 'Other',
        };
    }

    private function prefixSuffixSimilarity(string $first, string $second): float
    {
        $minimum = min(strlen($first), strlen($second));
        if ($minimum < 3) {
            return 0;
        }
        $prefix = 0;
        while ($prefix < $minimum && $first[$prefix] === $second[$prefix]) {
            $prefix++;
        }
        $suffix = 0;
        while ($suffix < $minimum && $first[strlen($first) - 1 - $suffix] === $second[strlen($second) - 1 - $suffix]) {
            $suffix++;
        }

        return min(100, (($prefix + $suffix) / max(strlen($first), strlen($second))) * 100);
    }

    private function plainText(string $value): string
    {
        $value = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /** @return array<int, string> */
    private function parseClasses(string $classField, string $description): array
    {
        $classes = [];
        foreach (preg_split('/\s*,\s*/', $classField, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $class) {
            if (preg_match('/^(?:[1-9]|[1-3][0-9]|4[0-5])$/', trim($class)) === 1) {
                $classes[] = (string) (int) trim($class);
            }
        }
        preg_match_all('/\[\s*class\s*:\s*([^\]]+)\]/iu', $description, $matches);
        foreach ($matches[1] ?? [] as $match) {
            foreach (preg_split('/\s*,\s*/', $match, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $class) {
                if (preg_match('/^(?:[1-9]|[1-3][0-9]|4[0-5])$/', trim($class)) === 1) {
                    $classes[] = (string) (int) trim($class);
                }
            }
        }

        return array_values(array_unique($classes));
    }

    private function isContaminatedDescription(string $description): bool
    {
        return preg_match('/View All Results|Search by Proprietor Name|Upgrade\s*₹/iu', $description) === 1;
    }
}
