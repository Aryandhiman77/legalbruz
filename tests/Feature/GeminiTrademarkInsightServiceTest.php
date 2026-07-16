<?php

namespace Tests\Feature;

use App\Services\GeminiTrademarkInsightService;
use App\Services\TrademarkProbabilityService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiTrademarkInsightServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'services.gemini.enabled' => true,
            'services.gemini.api_key' => 'test-secret-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
    }

    public function test_valid_structured_output_is_added_without_changing_numeric_analysis(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        $payload = $this->payload();
        $expected = (new TrademarkProbabilityService)->analyze('Amazon', $payload['data'], null);

        $response = $this->postJson(route('trademark.ai-probability'), $payload)->assertOk();

        $response->assertJsonPath('analysis.ai_insights.generated_by', 'gemini')
            ->assertJsonPath('analysis.ai_insights.summary', 'Select the proposed trademark class to calculate a class-specific registration estimate.')
            ->assertJsonPath('analysis.ai_insights.reasons.0.title', 'Exact name evidence')
            ->assertJsonPath('analysis.ai_insights.warnings.0.title', 'Trademark class required')
            ->assertJsonPath('analysis.registration_probability', $expected['registration_probability'])
            ->assertJsonPath('analysis.conflict_risk', $expected['conflict_risk'])
            ->assertJsonPath('analysis.factors', $expected['factors']);

        $this->assertStringNotContainsString('test-secret-key', $response->getContent());
    }

    public function test_missing_api_key_uses_fallback_output(): void
    {
        config(['services.gemini.api_key' => null]);
        Http::fake();

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('fallback', $insights['generated_by']);
        Http::assertNothingSent();
    }

    public function test_timeout_uses_fallback_output(): void
    {
        Http::fake(['*' => Http::failedConnection('timeout')]);

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('fallback', $insights['generated_by']);
    }

    public function test_rate_limit_response_uses_fallback_output(): void
    {
        Http::fake(['*' => Http::response([], 429)]);

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('fallback', $insights['generated_by']);
    }

    public function test_broken_json_uses_fallback_output(): void
    {
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => '{broken']]]]],
        ])]);

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('fallback', $insights['generated_by']);
    }

    public function test_no_selected_class_creates_an_actionable_warning(): void
    {
        config(['services.gemini.enabled' => false]);

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('Trademark class required', $insights['warnings'][0]['title']);
        $this->assertNotEmpty($insights['warnings'][0]['action']);
    }

    public function test_selected_class_does_not_create_no_class_warning(): void
    {
        config(['services.gemini.enabled' => false]);
        $analysis = $this->analysis('45');

        $insights = $this->service()->generate($analysis, $this->records());

        $this->assertSame([], $insights['warnings']);
    }

    public function test_internal_cleaning_never_creates_a_user_warning(): void
    {
        config(['services.gemini.enabled' => false]);
        $records = $this->records();
        $records[0]['description'] = 'View All Results Search by Proprietor Name Upgrade ₹';
        $analysis = (new TrademarkProbabilityService)->analyze('Amazon', $records, '45', 'Personal and social services');

        $insights = $this->service()->generate($analysis, $records);
        $json = json_encode($insights);

        $this->assertSame([], $analysis['warnings']);
        $this->assertStringNotContainsString('scraped data', strtolower($json));
        $this->assertStringNotContainsString('malformed scraped data', strtolower($json));
    }

    public function test_relevant_matches_are_limited_and_sensitive_fields_are_not_sent(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(warnings: []), 200)]);
        $records = [];
        for ($index = 1; $index <= 15; $index++) {
            $records[] = [
                'application_id' => (string) $index,
                'trademark_name' => "Amazon {$index}",
                'status' => 'Registered',
                'class' => '45',
                'type' => 'Word',
                'proprietor' => 'Example Owner',
                'description' => str_repeat('Service ', 100),
                'image_url' => 'https://private.example/image.png',
                'source_url' => 'https://private.example/source',
            ];
        }
        $analysis = (new TrademarkProbabilityService)->analyze('Amazon', $records, '45');

        $this->service()->generate($analysis, $records);

        Http::assertSent(function (Request $request): bool {
            $input = json_decode($request->data()['contents'][0]['parts'][0]['text'], true);
            $matches = $input['relevant_matches'];
            $encoded = json_encode($request->data());

            return count($matches) === 10
                && mb_strlen($matches[0]['description']) <= 300
                && ! str_contains($encoded, 'image_url')
                && ! str_contains($encoded, 'source_url')
                && ! str_contains($encoded, 'private.example');
        });
    }

    public function test_valid_empty_warnings_are_preserved_for_frontend_hiding(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(warnings: []), 200)]);

        $insights = $this->service()->generate($this->analysis('45'), $this->records());

        $this->assertSame('gemini', $insights['generated_by']);
        $this->assertSame([], $insights['warnings']);
    }

    public function test_gemini_output_cannot_soften_or_overwrite_a_hard_conflict(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(warnings: []), 200)]);
        $analysis = $this->analysis('45');

        $insights = $this->service()->generate($analysis, $this->records());

        $this->assertSame(99, $analysis['conflict_risk']);
        $this->assertSame(1, $analysis['registration_probability']);
        $this->assertTrue($analysis['hard_conflict']);
        $this->assertStringContainsString('major registration obstacle', $insights['summary']);
        $this->assertSame('Exact same-class conflict', $insights['reasons'][0]['title']);
        $this->assertNotContains('positive', array_column($insights['reasons'], 'impact'));
    }

    private function service(): GeminiTrademarkInsightService
    {
        return app(GeminiTrademarkInsightService::class);
    }

    /** @return array<string, mixed> */
    private function analysis(?string $class = null): array
    {
        return (new TrademarkProbabilityService)->analyze(
            'Amazon',
            $this->records(),
            $class,
            $class === null ? null : 'Personal and social services',
        );
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['keyword' => 'Amazon', 'class' => null, 'data' => $this->records()];
    }

    /** @return array<int, array<string, mixed>> */
    private function records(): array
    {
        return [[
            'application_id' => '2640730',
            'trademark_name' => 'Amazon',
            'status' => 'Registered',
            'class' => '45',
            'type' => 'Word',
            'proprietor' => 'Amazon Technologies Inc',
            'description' => 'Personal and social services',
        ]];
    }

    /** @return array<string, mixed> */
    private function geminiResponse(?array $warnings = null): array
    {
        $output = [
            'summary' => 'The fixed result shows a measured level of conflict risk.',
            'reasons' => [
                ['title' => 'Exact name evidence', 'detail' => 'The supplied result includes an exact active mark.', 'impact' => 'negative'],
                ['title' => 'Fixed score', 'detail' => 'The application calculated the displayed risk from the matching records.', 'impact' => 'neutral'],
            ],
            'warnings' => $warnings ?? [[
                'title' => 'Choose a class',
                'detail' => 'A class was not selected for this analysis.',
                'action' => 'Select the class related to the intended goods or services.',
            ]],
        ];

        return ['candidates' => [['content' => ['parts' => [['text' => json_encode($output)]]]]]];
    }
}
