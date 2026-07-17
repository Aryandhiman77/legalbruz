<?php

namespace Tests\Unit;

use App\Services\TrademarkProbabilityService;
use PHPUnit\Framework\TestCase;

class TrademarkProbabilityServiceTest extends TestCase
{
    private TrademarkProbabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrademarkProbabilityService;
    }

    public function test_empty_results_return_fixed_low_search_risk(): void
    {
        $analysis = $this->service->analyze('NewBrand', []);

        $this->assertSame(95, $analysis['registration_probability']);
        $this->assertSame(5, $analysis['conflict_risk']);
        $this->assertSame('Low', $analysis['risk_level']);
        $this->assertSame(75, $analysis['confidence_score']);
        $this->assertSame(0, $analysis['total_unique_marks']);
        $this->assertCount(6, $analysis['factors']);
        $this->assertSame([0, 0, 0, 0, 0, 0], array_column($analysis['factors'], 'score'));
    }

    public function test_name_spacing_and_punctuation_variations_are_exact(): void
    {
        foreach (['hiremyprofile', 'hire my profile', 'hire-my-profile', 'hire_my_profile', 'Hire My-Profile™'] as $name) {
            $this->assertSame(100.0, $this->service->nameSimilarity('hiremyprofile', $name));
        }
    }

    public function test_one_exact_active_word_mark_creates_at_least_eighty_two_risk(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')]);

        $this->assertGreaterThanOrEqual(82, $analysis['conflict_risk']);
        $this->assertSame(1, $analysis['exact_active_word_marks']);
        $this->assertSame($analysis['exact_active_word_marks'], $analysis['exact_registered_word_marks']);
    }

    public function test_several_exact_active_word_marks_are_critical(): void
    {
        $records = array_map(fn (int $id): array => $this->record((string) $id, 'Amazon'), range(1, 3));
        $analysis = $this->service->analyze('Amazon', $records);

        $this->assertGreaterThanOrEqual(92, $analysis['conflict_risk']);
        $this->assertSame('Critical', $analysis['risk_level']);
    }

    public function test_five_exact_active_word_marks_reach_maximum_conflict_floor(): void
    {
        $records = array_map(fn (int $id): array => $this->record((string) $id, 'Amazon'), range(1, 5));
        $analysis = $this->service->analyze('Amazon', $records);

        $this->assertSame(97, $analysis['conflict_risk']);
        $this->assertSame(3, $analysis['registration_probability']);
    }

    public function test_exact_active_device_marks_have_lower_floors_than_word_marks(): void
    {
        $one = $this->service->analyze('Amazon', [$this->record('1', 'Amazon', 'Registered', 'Device')]);
        $two = $this->service->analyze('Amazon', [
            $this->record('1', 'Amazon', 'Registered', 'Device'),
            $this->record('2', 'Amazon', 'Accepted', 'Logo'),
        ]);

        $this->assertGreaterThanOrEqual(40, $one['conflict_risk']);
        $this->assertGreaterThanOrEqual(55, $two['conflict_risk']);
        $this->assertLessThan(82, $one['conflict_risk']);
        $this->assertSame(2, $two['exact_active_device_marks']);
    }

    public function test_close_and_very_close_active_names_reduce_probability(): void
    {
        $veryClose = $this->service->analyze('Amazon', [$this->record('1', 'Amazone')]);
        $close = $this->service->analyze('Amazon', [$this->record('1', 'Amazin')]);

        $this->assertGreaterThan(5, $veryClose['conflict_risk']);
        $this->assertGreaterThan(5, $close['conflict_risk']);
        $this->assertGreaterThanOrEqual(1, $veryClose['very_close_active_marks'] + $veryClose['close_active_marks']);
    }

    public function test_phonetic_active_match_has_strong_floor(): void
    {
        $analysis = $this->service->analyze('Nike', [$this->record('1', 'Nyk')]);

        $this->assertTrue($this->service->phoneticMatch('Nike', 'Nyk'));
        $this->assertSame(1, $analysis['phonetic_active_matches']);
        $this->assertGreaterThanOrEqual(55, $analysis['conflict_risk']);
    }

    public function test_pending_exact_word_mark_uses_pending_floor(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon', 'Objected')]);

        $this->assertSame(0, $analysis['exact_active_word_marks']);
        $this->assertSame(1, $analysis['exact_pending_word_marks']);
        $this->assertGreaterThanOrEqual(60, $analysis['conflict_risk']);
    }

    public function test_inactive_exact_mark_does_not_receive_active_weight(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon', 'Abandoned')]);

        $this->assertSame(0, $analysis['active_marks']);
        $this->assertSame(1, $analysis['inactive_marks']);
        $this->assertSame(1, $analysis['inactive_exact_marks']);
        $this->assertLessThanOrEqual(20, $analysis['conflict_risk']);
    }

    public function test_active_class_spread_is_unique_and_scored(): void
    {
        $records = [];
        foreach ([9, 35, 42, 45] as $index => $class) {
            $record = $this->record((string) $index, 'Different '.($index + 1));
            $record['class'] = (string) $class;
            $records[] = $record;
        }
        $analysis = $this->service->analyze('Amazon', $records);
        $factor = collect($analysis['factors'])->firstWhere('key', 'class_spread');

        $this->assertSame(4, $analysis['unique_active_classes']);
        $this->assertSame(65, $factor['score']);
    }

    public function test_duplicate_application_ids_are_removed(): void
    {
        $analysis = $this->service->analyze('Amazon', [
            $this->record('1', 'Amazon'),
            $this->record('1', 'Amazon', 'Accepted', 'Device'),
        ]);

        $this->assertSame(1, $analysis['total_unique_marks']);
    }

    public function test_missing_ids_use_stable_fingerprints(): void
    {
        $first = $this->record('', 'Amazon');
        $second = $first;
        $analysis = $this->service->analyze('Amazon', [$first, $second]);

        $this->assertSame(1, $analysis['total_unique_marks']);
    }

    public function test_broken_description_is_silently_ignored(): void
    {
        $record = $this->record('1', 'Amazon');
        $record['description'] = 'View All Results Search by Proprietor Name Upgrade ₹';
        $cleaned = $this->service->cleanRecords([$record]);

        $this->assertSame('', $cleaned['records'][0]['description']);
        $this->assertSame([], $cleaned['warnings']);
    }

    public function test_probability_and_conflict_always_total_one_hundred_without_one_hundred_probability(): void
    {
        $analyses = [
            $this->service->analyze('NewBrand', []),
            $this->service->analyze('Amazon', [$this->record('1', 'Amazon')]),
            $this->service->analyze('Amazon', [$this->record('1', 'Different', 'Expired')]),
        ];
        foreach ($analyses as $analysis) {
            $this->assertSame(100, $analysis['registration_probability'] + $analysis['conflict_risk']);
            $this->assertLessThan(100, $analysis['registration_probability']);
            $this->assertGreaterThanOrEqual(3, $analysis['registration_probability']);
        }
    }

    public function test_confidence_reflects_source_and_record_completeness(): void
    {
        $thirdPartyEmpty = $this->service->analyze('NewBrand', []);
        $thirdPartyComplete = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')]);
        $official = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')], ['source_type' => 'official']);

        $this->assertSame(75, $thirdPartyEmpty['confidence_score']);
        $this->assertSame(80, $thirdPartyComplete['confidence_score']);
        $this->assertSame(95, $official['confidence_score']);
    }

    public function test_v4_cache_key_distinguishes_empty_and_non_empty_results(): void
    {
        $empty = $this->service->cacheKey('Amazon', [], ['gemini_model' => 'model', 'insight_schema_version' => 'v4']);
        $records = $this->service->cacheKey('Amazon', [$this->record('1', 'Amazon')], ['gemini_model' => 'model', 'insight_schema_version' => 'v4']);

        $this->assertStringStartsWith('trademark-probability-v4:', $empty);
        $this->assertNotSame($empty, $records);
        $this->assertStringNotContainsString('trademark-probability-v3', $empty);
    }

    /** @return array<string, string|null> */
    private function record(string $id, string $name, string $status = 'Registered', string $type = 'Word'): array
    {
        return [
            'application_id' => $id,
            'trademark_name' => $name,
            'status' => $status,
            'class' => '45',
            'type' => $type,
            'proprietor' => 'Example Proprietor',
            'description' => 'Example services',
        ];
    }
}
