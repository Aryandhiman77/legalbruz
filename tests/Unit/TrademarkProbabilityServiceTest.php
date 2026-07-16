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

    public function test_exact_registered_word_mark_creates_high_risk(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')], '45');

        $this->assertSame(95, $analysis['conflict_risk']);
        $this->assertSame('Very High', $analysis['risk_level']);
        $this->assertTrue($analysis['hard_conflict']);
        $this->assertSame(1, $analysis['exact_registered_word_marks']);
    }

    public function test_several_exact_active_marks_result_in_very_high_risk(): void
    {
        $records = [];
        for ($index = 1; $index <= 8; $index++) {
            $records[] = $this->record((string) $index, 'Amazon');
        }
        $records[] = $this->record('device', 'Amazon', 'Registered', 'Device');
        $records[] = $this->record('opposed-1', 'Amazon', 'Opposed');
        $records[] = $this->record('opposed-2', 'Amazon', 'Objected');

        $analysis = $this->service->analyze('Amazon', $records, '45');

        $this->assertSame(95, $analysis['conflict_risk']);
        $this->assertSame('Very High', $analysis['risk_level']);
    }

    public function test_near_trademark_name_is_detected(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazone')]);

        $this->assertSame(1, $analysis['similar_registered_marks']);
        $this->assertGreaterThanOrEqual(80, $this->service->nameSimilarity('Amazon', 'Amazone'));
    }

    public function test_duplicate_application_ids_are_removed(): void
    {
        $analysis = $this->service->analyze('Amazon', [
            $this->record('1', 'Amazon'),
            $this->record('1', 'Amazon Device', 'Registered', 'Device'),
        ]);

        $this->assertSame(1, $analysis['total_unique_marks']);
    }

    public function test_malformed_description_does_not_break_analysis(): void
    {
        $record = $this->record('1', 'Amazon');
        $record['description'] = 'View All Results Search by Proprietor Name Upgrade ₹';

        $analysis = $this->service->analyze('Amazon', [$record], '45', 'Retail technology');

        $this->assertSame([], $analysis['warnings']);
        $this->assertSame(1, $analysis['total_unique_marks']);
    }

    public function test_proprietor_status_suffix_is_removed(): void
    {
        $record = $this->record('1', 'Amazon', 'Refused');
        $record['proprietor'] = 'Sanjeevani Biotech Refused';

        $cleaned = $this->service->cleanRecords([$record]);

        $this->assertSame('Sanjeevani Biotech', $cleaned['records'][0]['proprietor']);
    }

    public function test_class_specific_matches_are_counted_from_fields_and_descriptions(): void
    {
        $first = $this->record('1', 'Amazon');
        $first['class'] = '4, 45';
        $second = $this->record('2', 'Amazon Device', 'Registered', 'Device');
        $second['class'] = null;
        $second['description'] = 'Goods and services [Class : 4]';

        $analysis = $this->service->analyze('Amazon', [$first, $second], '4');

        $this->assertSame(2, $analysis['same_class_registered_marks']);
    }

    public function test_no_class_returns_null_class_score(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')]);
        $classFactor = collect($analysis['factors'])->firstWhere('key', 'class_conflict');

        $this->assertNull($analysis['same_class_registered_marks']);
        $this->assertNull($classFactor['score']);
        $this->assertNull($analysis['registration_probability']);
        $this->assertNull($analysis['conflict_risk']);
        $this->assertSame('Class Required', $analysis['risk_level']);
        $this->assertSame('preliminary', $analysis['analysis_mode']);
        $this->assertTrue($analysis['class_required']);
    }

    public function test_result_is_clamped_and_probabilities_total_one_hundred(): void
    {
        $low = $this->service->analyze('Unique', [$this->record('1', 'Different', 'Expired')], '45', 'Software consulting');
        $high = $this->service->analyze('Amazon', array_map(
            fn (int $index): array => $this->record((string) $index, 'Amazon'),
            range(1, 20),
        ), '45', 'Example services');

        foreach ([$low, $high] as $analysis) {
            $this->assertGreaterThanOrEqual(5, $analysis['conflict_risk']);
            $this->assertLessThanOrEqual(99, $analysis['conflict_risk']);
            $this->assertSame(100, $analysis['registration_probability'] + $analysis['conflict_risk']);
        }
    }

    public function test_exact_strong_word_mark_with_overlapping_description_forces_critical_conflict(): void
    {
        $record = $this->record('1', 'HireMyProfile™');
        $record['class'] = '35';
        $record['description'] = 'Recruitment, employment listing and job placement services';

        $analysis = $this->service->analyze(
            ' hiremyprofile® ',
            [$record],
            '35',
            'Recruitment employment listing and job placement services',
        );

        $this->assertSame(99, $analysis['conflict_risk']);
        $this->assertSame(1, $analysis['registration_probability']);
        $this->assertSame('Critical', $analysis['risk_level']);
        $this->assertTrue($analysis['hard_conflict']);
        $this->assertSame(1, $analysis['exact_same_class_word_marks']);
        $this->assertGreaterThanOrEqual(80, $analysis['highest_description_similarity']);
    }

    public function test_exact_strong_word_mark_without_description_has_minimum_ninety_five_risk(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon')], '45');

        $this->assertGreaterThanOrEqual(95, $analysis['conflict_risk']);
        $this->assertLessThanOrEqual(5, $analysis['registration_probability']);
        $this->assertTrue($analysis['hard_conflict']);
        $this->assertSame('limited', $analysis['analysis_quality']);
        $this->assertSame('Goods or services not entered', $analysis['warnings'][0]['title']);
    }

    public function test_exact_active_device_mark_with_strong_overlap_has_minimum_ninety_risk(): void
    {
        $record = $this->record('1', 'Amazon', 'Accepted', 'Device');
        $record['description'] = 'Online retail and marketplace technology services';

        $analysis = $this->service->analyze('Amazon', [$record], '45', 'Online retail and marketplace technology services');

        $this->assertGreaterThanOrEqual(90, $analysis['conflict_risk']);
        $this->assertSame(1, $analysis['exact_same_class_device_marks']);
        $this->assertSame('Device mark review required', $analysis['warnings'][0]['title']);
    }

    public function test_exact_mark_in_another_class_does_not_trigger_hard_conflict(): void
    {
        $record = $this->record('1', 'Amazon');
        $record['class'] = '9';

        $analysis = $this->service->analyze('Amazon', [$record], '45', 'Legal services');

        $this->assertFalse($analysis['hard_conflict']);
        $this->assertSame(0, $analysis['exact_same_class_word_marks']);
    }

    public function test_inactive_same_class_mark_does_not_trigger_hard_conflict(): void
    {
        $analysis = $this->service->analyze('Amazon', [$this->record('1', 'Amazon', 'Abandoned')], '45', 'Example services');

        $this->assertFalse($analysis['hard_conflict']);
        $this->assertSame(0, $analysis['exact_same_class_word_marks']);
    }

    public function test_description_similarity_ignores_weak_words_and_detects_shared_terms(): void
    {
        $strong = $this->service->descriptionSimilarity(
            'Recruitment, employment listing and job placement services',
            'Services namely recruitment employment listing and job placement',
        );
        $weak = $this->service->descriptionSimilarity('Business services and other goods', 'Legal services included in class 45');

        $this->assertGreaterThanOrEqual(80, $strong);
        $this->assertLessThan(40, $weak);
    }

    public function test_cache_key_uses_new_schema_and_proposed_description(): void
    {
        $records = [$this->record('1', 'Amazon')];
        $first = $this->service->cacheKey('Amazon', '45', $records, 'Retail services');
        $second = $this->service->cacheKey('Amazon', '45', $records, 'Cloud hosting');

        $this->assertStringStartsWith('trademark-probability-v3:', $first);
        $this->assertNotSame($first, $second);
        $this->assertStringNotContainsString('trademark-probability:v2:', $first);
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
