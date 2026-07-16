<?php

namespace App\Services;

class TrademarkProbabilityService
{
    private const SCORING_SCHEMA_VERSION = 'trademark-probability-v3';

    private const STRONG_STATUSES = [
        'registered',
        'accepted',
        'accepted & advertised',
        'advertised before accepted',
    ];

    private const MEDIUM_STATUSES = [
        'opposed',
        'objected',
        'formalities chk pass',
        'marked for exam',
        'exam report issued',
        'send to vienna codification',
    ];

    private const INACTIVE_STATUSES = [
        'refused',
        'abandoned',
        'removed',
        'withdrawn',
        'cancelled',
        'expired',
    ];

    private const WEAK_DESCRIPTION_WORDS = [
        'services', 'service', 'goods', 'providing', 'provide', 'provided',
        'business', 'included', 'class', 'related', 'namely', 'other',
    ];

    private const DISCLAIMER = 'This automated estimate compares available trademark records, classes, statuses and goods or services. It is not legal advice and does not guarantee acceptance or refusal by the Trademark Registry.';

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<string, mixed>
     */
    public function analyze(string $keyword, array $records, ?string $requestedClass = null, ?string $proposedDescription = null): array
    {
        $normalizedKeyword = $this->normalizeName($keyword);
        $requestedClass = $this->normalizeRequestedClass($requestedClass);
        $proposedDescription = $this->normalizeProposedDescription($proposedDescription);
        ['records' => $cleanRecords] = $this->cleanRecords($records);

        $counts = [
            'exact_registered_word_marks' => 0,
            'exact_registered_device_marks' => 0,
            'similar_registered_marks' => 0,
            'same_class_registered_marks' => $requestedClass === null ? null : 0,
            'opposed_or_objected_marks' => 0,
            'active_marks' => 0,
            'inactive_marks' => 0,
            'total_unique_marks' => count($cleanRecords),
        ];
        $exactActiveMatches = 0;
        $nearActiveMatches = 0;
        $phoneticActiveMatches = 0;
        $exactSameClassWordMarks = 0;
        $exactSameClassDeviceMarks = 0;
        $highestNameSimilarity = 0.0;
        $highestDescriptionSimilarity = 0.0;
        $sameClassCandidates = [];

        foreach ($cleanRecords as $record) {
            $status = $record['normalized_status'];
            $isRegistered = $status === 'registered';
            $isStrong = in_array($status, self::STRONG_STATUSES, true);
            $isActive = $isStrong || in_array($status, self::MEDIUM_STATUSES, true);
            $isInactive = in_array($status, self::INACTIVE_STATUSES, true);
            $isExact = $record['normalized_name'] === $normalizedKeyword;
            $similarity = $this->nameSimilarity($normalizedKeyword, $record['normalized_name']);
            $isNear = ! $isExact && $similarity >= 80;
            $isPhonetic = ! $isExact && $this->phoneticMatch($normalizedKeyword, $record['normalized_name']);
            $isSameClass = $requestedClass !== null && in_array($requestedClass, $record['classes'], true);
            $isWord = $this->isWordMark($record['type']);
            $isDevice = $this->isDeviceMark($record['type']);

            if ($isActive) {
                $counts['active_marks']++;
                $exactActiveMatches += (int) $isExact;
                $nearActiveMatches += (int) $isNear;
                $phoneticActiveMatches += (int) $isPhonetic;
            }
            if ($isInactive) {
                $counts['inactive_marks']++;
            }
            if (in_array($status, ['opposed', 'objected'], true)) {
                $counts['opposed_or_objected_marks']++;
            }
            if ($isRegistered && $isExact && $isWord) {
                $counts['exact_registered_word_marks']++;
            }
            if ($isRegistered && $isExact && $isDevice) {
                $counts['exact_registered_device_marks']++;
            }
            if ($isNear && $isActive) {
                $counts['similar_registered_marks']++;
            }
            if ($isSameClass && $isRegistered) {
                $counts['same_class_registered_marks']++;
            }

            if ($isSameClass && $isActive) {
                $descriptionSimilarity = $proposedDescription === null
                    ? 0.0
                    : $this->descriptionSimilarity($proposedDescription, $record['description']);
                $highestNameSimilarity = max($highestNameSimilarity, $similarity);
                $highestDescriptionSimilarity = max($highestDescriptionSimilarity, $descriptionSimilarity);
                $exactSameClassWordMarks += (int) ($isExact && $isWord);
                $exactSameClassDeviceMarks += (int) ($isExact && $isDevice);
                $sameClassCandidates[] = [
                    'exact' => $isExact,
                    'word' => $isWord,
                    'device' => $isDevice,
                    'strong' => $isStrong,
                    'name_similarity' => $similarity,
                    'description_similarity' => $descriptionSimilarity,
                ];
            }
        }

        $base = [
            'keyword' => trim($keyword),
            'analysis_mode' => $requestedClass === null ? 'preliminary' : 'class_specific',
            'analysis_quality' => $requestedClass !== null && $proposedDescription !== null ? 'standard' : 'limited',
            'class_required' => $requestedClass === null,
            'requested_class' => $requestedClass,
            'proposed_description' => $proposedDescription,
            'hard_conflict' => false,
            'hard_conflict_reason' => null,
            'exact_same_class_word_marks' => $requestedClass === null ? null : $exactSameClassWordMarks,
            'exact_same_class_device_marks' => $requestedClass === null ? null : $exactSameClassDeviceMarks,
            'highest_name_similarity' => $requestedClass === null ? null : (int) round($highestNameSimilarity),
            'highest_description_similarity' => $requestedClass === null || $proposedDescription === null ? null : (int) round($highestDescriptionSimilarity),
            ...$counts,
        ];

        if ($requestedClass === null) {
            return [
                ...$base,
                'registration_probability' => null,
                'conflict_risk' => null,
                'risk_level' => 'Class Required',
                'factors' => $this->factors($counts, null, $exactActiveMatches),
                'reasons' => $this->reasons($counts, null, $phoneticActiveMatches),
                'warnings' => [$this->classRequiredWarning()],
                'disclaimer' => self::DISCLAIMER,
            ];
        }

        $exactScore = $counts['exact_registered_word_marks'] > 0
            ? 55 + min(20, max(0, $counts['exact_registered_word_marks'] - 1) * 4)
            : 0;
        $deviceScore = min(10, $counts['exact_registered_device_marks'] * 3);
        $similarScore = min(15, $counts['similar_registered_marks'] * 3);
        $classScore = $counts['same_class_registered_marks'] > 0
            ? min(20, 10 + ($counts['same_class_registered_marks'] * 3))
            : 0;
        $oppositionScore = min(10, $counts['opposed_or_objected_marks'] * 5);
        $conflictRisk = $exactScore + $deviceScore + $similarScore + $classScore + $oppositionScore;

        if ($exactActiveMatches === 0 && $nearActiveMatches === 0) {
            $conflictRisk -= 10;
        }

        $conflictRisk = max(5, min(95, $conflictRisk));
        $hardConflict = false;
        $hardConflictReason = null;
        $deviceReviewRequired = false;

        foreach ($sameClassCandidates as $candidate) {
            $nameSimilarity = $candidate['name_similarity'];
            $descriptionSimilarity = $candidate['description_similarity'];

            if ($candidate['exact'] && $candidate['word'] && $candidate['strong']) {
                if ($proposedDescription === null) {
                    $conflictRisk = max($conflictRisk, 95);
                    $hardConflict = true;
                    $hardConflictReason = 'An active exact Word trademark was found in the selected class. Goods or services were not supplied for a fuller overlap comparison.';
                } elseif ($descriptionSimilarity >= 80) {
                    $conflictRisk = 99;
                    $hardConflict = true;
                    $hardConflictReason = 'An active exact Word trademark was found in the selected class for identical or strongly overlapping goods or services.';
                }
            }

            if ($candidate['exact'] && $candidate['device']) {
                $deviceReviewRequired = true;
                if ($descriptionSimilarity >= 80) {
                    $conflictRisk = max($conflictRisk, 90);
                }
            }

            if ($nameSimilarity >= 90 && $nameSimilarity < 100 && $descriptionSimilarity >= 80) {
                $conflictRisk = max($conflictRisk, 90);
            } elseif ($nameSimilarity >= 80 && $nameSimilarity < 90 && $descriptionSimilarity >= 70) {
                $conflictRisk = max($conflictRisk, 75);
            } elseif ($nameSimilarity >= 70 && $nameSimilarity < 80 && $descriptionSimilarity >= 60) {
                $conflictRisk = max($conflictRisk, 60);
            }
        }

        $conflictRisk = min(99, $conflictRisk);
        $registrationProbability = 100 - $conflictRisk;
        $warnings = [];
        if ($proposedDescription === null) {
            $warnings[] = $this->descriptionRequiredWarning();
        }
        if ($deviceReviewRequired) {
            $warnings[] = [
                'title' => 'Device mark review required',
                'detail' => 'An active Device mark with the same name was found in the selected class, but visual logo similarity was not checked.',
                'action' => 'Review the existing logo and compare it with the proposed device mark.',
            ];
        }

        return [
            ...$base,
            'hard_conflict' => $hardConflict,
            'hard_conflict_reason' => $hardConflictReason,
            'registration_probability' => $registrationProbability,
            'conflict_risk' => $conflictRisk,
            'risk_level' => $hardConflict && $conflictRisk === 99 ? 'Critical' : $this->riskLevel($conflictRisk),
            'factors' => $this->factors($counts, $requestedClass, $exactActiveMatches),
            'reasons' => $this->reasons($counts, $requestedClass, $phoneticActiveMatches),
            'warnings' => $warnings,
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
            $name = $this->normalizeName((string) ($record['trademark_name'] ?? ''));
            $status = $this->normalizeStatus((string) ($record['status'] ?? ''));
            $description = mb_substr($this->plainText((string) ($record['description'] ?? '')), 0, 2000);

            if ($this->isContaminatedDescription($description)) {
                $description = '';
            }

            $proprietor = trim((string) ($record['proprietor'] ?? ''));
            if ($status !== '' && preg_match('/\s+'.preg_quote($status, '/').'\s*$/iu', $proprietor) === 1) {
                $proprietor = trim((string) preg_replace('/\s+'.preg_quote($status, '/').'\s*$/iu', '', $proprietor));
            }

            $classes = $this->parseClasses((string) ($record['class'] ?? ''), $description);
            $applicationId = trim((string) ($record['application_id'] ?? ''));
            $dedupeKey = $applicationId !== ''
                ? 'id:'.mb_strtolower($applicationId)
                : 'record:'.hash('sha256', implode('|', [$name, implode(',', $classes), mb_strtolower(trim((string) ($record['type'] ?? ''))), mb_strtolower($proprietor)]));

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $clean[] = [
                'application_id' => $applicationId,
                'normalized_name' => $name,
                'normalized_status' => $status,
                'classes' => $classes,
                'type' => trim((string) ($record['type'] ?? '')),
                'proprietor' => $proprietor,
                'description' => $description,
            ];
        }

        return ['records' => $clean, 'warnings' => []];
    }

    public function nameSimilarity(string $first, string $second): float
    {
        $first = $this->normalizeName($first);
        $second = $this->normalizeName($second);
        $maxLength = max(strlen($first), strlen($second));

        if ($maxLength === 0) {
            return 100.0;
        }

        $levenshtein = (1 - (levenshtein($first, $second) / $maxLength)) * 100;
        similar_text($first, $second, $similarText);

        return round(max(0, $levenshtein, $similarText), 2);
    }

    public function descriptionSimilarity(string $first, string $second): float
    {
        $firstTokens = $this->descriptionTokens($first);
        $secondTokens = $this->descriptionTokens($second);
        if ($firstTokens === [] || $secondTokens === []) {
            return 0.0;
        }

        $intersection = array_intersect($firstTokens, $secondTokens);
        $union = array_unique([...$firstTokens, ...$secondTokens]);
        $jaccard = count($intersection) / max(1, count($union)) * 100;
        $coverage = count($intersection) / max(1, min(count($firstTokens), count($secondTokens))) * 100;
        similar_text(implode(' ', $firstTokens), implode(' ', $secondTokens), $similarText);
        $tokenScore = ($jaccard * 0.6) + ($coverage * 0.4);

        return round(min(100, max($similarText, $tokenScore)), 2);
    }

    public function phoneticMatch(string $first, string $second): bool
    {
        if (preg_match('/[a-z]/i', $first) !== 1 || preg_match('/[a-z]/i', $second) !== 1) {
            return false;
        }

        $firstMetaphone = metaphone($first);
        $secondMetaphone = metaphone($second);

        return $firstMetaphone !== '' && $firstMetaphone === $secondMetaphone;
    }

    /** @param array<int, array<string, mixed>> $records */
    public function cacheKey(string $keyword, ?string $requestedClass, array $records, ?string $proposedDescription = null): string
    {
        ['records' => $cleanRecords] = $this->cleanRecords($records);
        $parts = array_map(fn (array $record): string => implode('|', [
            $record['application_id'],
            $record['normalized_name'],
            $record['normalized_status'],
            implode(',', $record['classes']),
            mb_strtolower($record['type']),
            $this->normalizeDescription($record['description']),
        ]), $cleanRecords);
        sort($parts, SORT_STRING);

        return self::SCORING_SCHEMA_VERSION.':'.hash('sha256', implode('::', [
            $this->normalizeName($keyword),
            $this->normalizeRequestedClass($requestedClass) ?? '',
            $this->normalizeDescription((string) $proposedDescription),
            implode(';;', $parts),
        ]));
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

    private function normalizeRequestedClass(?string $class): ?string
    {
        $class = trim((string) $class);
        if ($class === '' || strcasecmp($class, 'All Classes') === 0) {
            return null;
        }

        return $class;
    }

    private function normalizeProposedDescription(?string $description): ?string
    {
        $description = trim($this->plainText((string) $description));

        return $description === '' ? null : mb_substr($description, 0, 2000);
    }

    private function normalizeDescription(string $description): string
    {
        return implode(' ', $this->descriptionTokens($description));
    }

    /** @return array<int, string> */
    private function descriptionTokens(string $description): array
    {
        $normalized = mb_strtolower($this->plainText($description));
        $normalized = preg_replace('/\[\s*class\s*:\s*[^\]]+\]/iu', ' ', $normalized) ?? '';
        $normalized = preg_replace('/[^\pL\pN\s]/u', ' ', $normalized) ?? '';
        $tokens = preg_split('/\s+/u', trim($normalized), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_filter($tokens, fn (string $token): bool => mb_strlen($token) > 2 && ! in_array($token, self::WEAK_DESCRIPTION_WORDS, true));

        return array_values(array_unique($tokens));
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
            $class = trim($class);
            if ($class !== '' && strcasecmp($class, 'Unclassified') !== 0) {
                $classes[] = $class;
            }
        }
        preg_match_all('/\[\s*class\s*:\s*([^\]]+)\]/iu', $description, $matches);
        foreach ($matches[1] ?? [] as $match) {
            foreach (preg_split('/\s*,\s*/', $match, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $class) {
                $classes[] = trim($class);
            }
        }

        return array_values(array_unique(array_filter($classes)));
    }

    private function isContaminatedDescription(string $description): bool
    {
        return preg_match('/View All Results|Search by Proprietor Name|Upgrade\s*₹/iu', $description) === 1;
    }

    private function isWordMark(string $type): bool
    {
        return in_array(mb_strtolower(trim($type)), ['word', 'wordmark', 'word mark'], true);
    }

    private function isDeviceMark(string $type): bool
    {
        return in_array(mb_strtolower(trim($type)), ['device', 'logo', 'label', 'device mark'], true);
    }

    private function riskLevel(int $risk): string
    {
        return match (true) {
            $risk <= 29 => 'Low',
            $risk <= 59 => 'Medium',
            $risk <= 79 => 'High',
            default => 'Very High',
        };
    }

    private function percentage(int $count, int $total, int $minimum): int
    {
        if ($count === 0 || $total === 0) {
            return 0;
        }

        return min(100, max($minimum, (int) round(($count / $total) * 100)));
    }

    /** @param array<string, int|null> $counts */
    private function factors(array $counts, ?string $requestedClass, int $exactActiveMatches): array
    {
        return [
            ['key' => 'exact_name', 'label' => 'Exact Name Conflict', 'score' => $this->percentage($exactActiveMatches, (int) $counts['total_unique_marks'], $exactActiveMatches > 0 ? 75 : 0)],
            ['key' => 'registered_marks', 'label' => 'Registered Marks', 'score' => $this->percentage((int) $counts['exact_registered_word_marks'] + (int) $counts['exact_registered_device_marks'], (int) $counts['total_unique_marks'], $counts['exact_registered_word_marks'] > 0 ? 70 : 0)],
            ['key' => 'similar_names', 'label' => 'Similar Names', 'score' => $this->percentage((int) $counts['similar_registered_marks'], (int) $counts['total_unique_marks'], 0)],
            ['key' => 'class_conflict', 'label' => 'Class Conflict', 'score' => $requestedClass === null ? null : $this->percentage((int) $counts['same_class_registered_marks'], (int) $counts['total_unique_marks'], 0)],
        ];
    }

    /** @param array<string, int|null> $counts */
    private function reasons(array $counts, ?string $requestedClass, int $phoneticActiveMatches): array
    {
        $reasons = [];
        if ($counts['exact_registered_word_marks'] > 1) {
            $reasons[] = 'Several exact registered Word marks were found.';
        } elseif ($counts['exact_registered_word_marks'] === 1) {
            $reasons[] = 'An exact registered Word mark was found.';
        }
        if ($counts['exact_registered_device_marks'] > 0) {
            $reasons[] = 'Registered Device marks with the same name were found.';
        }
        if ($counts['similar_registered_marks'] > 0) {
            $reasons[] = $counts['similar_registered_marks'] === 1
                ? 'A similar active trademark name was found.'
                : 'Several similar active trademark names were found.';
        }
        if ($phoneticActiveMatches > 0 && $counts['similar_registered_marks'] === 0) {
            $reasons[] = 'An active mark with a phonetic name match was found.';
        }
        if ($requestedClass === null) {
            $reasons[] = 'Class-specific risk was not checked because no class was selected.';
        } elseif ($counts['same_class_registered_marks'] > 0) {
            $reasons[] = 'Active marks were found in the requested trademark class.';
        } else {
            $reasons[] = 'No registered marks were found in the requested trademark class.';
        }
        if ($counts['opposed_or_objected_marks'] > 0) {
            $reasons[] = 'Opposed or Objected trademark applications add conflict risk.';
        }
        if ($counts['active_marks'] === 0) {
            $reasons[] = 'No active trademark conflicts were found in the supplied records.';
        }

        return $reasons;
    }

    /** @return array{title: string, detail: string, action: string} */
    private function classRequiredWarning(): array
    {
        return [
            'title' => 'Trademark class required',
            'detail' => 'A final registration estimate cannot be calculated without checking the proposed goods or services inside the correct trademark class.',
            'action' => 'Select the correct trademark class and run the analysis again.',
        ];
    }

    /** @return array{title: string, detail: string, action: string} */
    private function descriptionRequiredWarning(): array
    {
        return [
            'title' => 'Goods or services not entered',
            'detail' => 'The selected class was checked, but the description of the proposed goods or services was not compared.',
            'action' => 'Enter the goods or services you plan to register for a more accurate estimate.',
        ];
    }
}
