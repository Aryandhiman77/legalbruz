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

    public function test_gemini_explains_without_changing_scores_counts_or_factors(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse(), 200)]);
        $payload = $this->payload();
        $expected = (new TrademarkProbabilityService)->analyze('Amazon', $payload['data'], [
            'source_type' => 'third_party',
        ]);

        $response = $this->postJson(route('trademark.ai-probability'), $payload)->assertOk();

        $response->assertJsonPath('analysis.ai_insights.generated_by', 'gemini')
            ->assertJsonPath('analysis.ai_insights.summary', 'The supplied search result has substantial earlier-mark conflict evidence.')
            ->assertJsonPath('analysis.registration_probability', $expected['registration_probability'])
            ->assertJsonPath('analysis.conflict_risk', $expected['conflict_risk'])
            ->assertJsonPath('analysis.risk_level', $expected['risk_level'])
            ->assertJsonPath('analysis.exact_active_word_marks', $expected['exact_active_word_marks'])
            ->assertJsonPath('analysis.factors', $expected['factors']);
        $this->assertStringNotContainsString('test-secret-key', $response->getContent());
    }

    public function test_request_tells_gemini_numeric_values_are_fixed_and_sends_all_factors(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(), 200)]);

        $this->service()->generate($this->analysis(), $this->records());

        Http::assertSent(function (Request $request): bool {
            $system = $request->data()['system_instruction']['parts'][0]['text'];
            $input = json_decode($request->data()['contents'][0]['parts'][0]['text'], true);

            return str_contains($system, 'numeric score and all counts have already been calculated by Laravel')
                && count($input['calculated_result']['factors']) === 6
                && array_key_exists('confidence_score', $input['calculated_result'])
                && count($input['relevant_records']) <= 10;
        });
    }

    public function test_missing_key_uses_local_fallback_without_http_request(): void
    {
        config(['services.gemini.api_key' => null]);
        Http::fake();

        $insights = $this->service()->generate($this->analysis(), $this->records());

        $this->assertSame('fallback', $insights['generated_by']);
        $this->assertNotEmpty($insights['reasons']);
        Http::assertNothingSent();
    }

    public function test_timeout_and_invalid_json_use_fallback_without_losing_analysis(): void
    {
        Http::fake(['*' => Http::failedConnection('timeout')]);
        $timeout = $this->service()->generate($this->analysis(), $this->records());
        $this->assertSame('fallback', $timeout['generated_by']);

        Cache::flush();
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{bad']]]]]], 200)]);
        $invalid = $this->service()->generate($this->analysis(), $this->records());
        $this->assertSame('fallback', $invalid['generated_by']);
    }

    public function test_zero_results_have_useful_fallback_reason_and_warning(): void
    {
        config(['services.gemini.enabled' => false]);
        $analysis = (new TrademarkProbabilityService)->analyze('NewBrand', []);

        $insights = $this->service()->generate($analysis, []);

        $this->assertSame('No matching record found', $insights['reasons'][0]['title']);
        $this->assertSame('Search estimate only', $insights['warnings'][0]['title']);
        $this->assertStringNotContainsString('guarantee', strtolower($insights['summary']));
    }

    public function test_exact_word_device_and_similar_fallback_reasons_are_specific(): void
    {
        config(['services.gemini.enabled' => false]);
        $records = [
            $this->record('1', 'Amazon', 'Registered', 'Word'),
            $this->record('2', 'Amazon', 'Accepted', 'Device'),
            $this->record('3', 'Amazone', 'Registered', 'Word'),
        ];
        $insights = $this->service()->generate((new TrademarkProbabilityService)->analyze('Amazon', $records), $records);
        $titles = array_column($insights['reasons'], 'title');

        $this->assertContains('Exact active Word marks', $titles);
        $this->assertContains('Exact active Device marks', $titles);
        $this->assertContains('Similar active names', $titles);
    }

    public function test_class_selection_or_internal_scraper_language_is_rejected(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(
            summary: 'Choose a trademark class because the scraped data may be malformed.'
        ), 200)]);

        $insights = $this->service()->generate($this->analysis(), $this->records());
        $encoded = strtolower(json_encode($insights));

        $this->assertSame('fallback', $insights['generated_by']);
        $this->assertStringNotContainsString('scrap', $encoded);
        $this->assertStringNotContainsString('malformed', $encoded);
        $this->assertStringNotContainsString('choose a trademark class', $encoded);
    }

    public function test_relevant_records_are_deduplicated_limited_and_sensitive_urls_are_not_sent(): void
    {
        Http::fake(['*' => Http::response($this->geminiResponse(), 200)]);
        $records = [];
        for ($index = 1; $index <= 15; $index++) {
            $records[] = [
                ...$this->record((string) $index, "Amazon {$index}"),
                'image_url' => 'https://private.example/image.png',
                'source_url' => 'https://private.example/source',
            ];
        }
        $records[] = $records[0];
        $analysis = (new TrademarkProbabilityService)->analyze('Amazon', $records);

        $this->service()->generate($analysis, $records);

        Http::assertSent(function (Request $request): bool {
            $input = json_decode($request->data()['contents'][0]['parts'][0]['text'], true);
            $encoded = json_encode($request->data());

            return count($input['relevant_records']) === 10
                && ! str_contains($encoded, 'image_url')
                && ! str_contains($encoded, 'source_url')
                && ! str_contains($encoded, 'private.example');
        });
    }

    private function service(): GeminiTrademarkInsightService
    {
        return app(GeminiTrademarkInsightService::class);
    }

    /** @return array<string, mixed> */
    private function analysis(): array
    {
        return (new TrademarkProbabilityService)->analyze('Amazon', $this->records());
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['keyword' => 'Amazon', 'source_type' => 'third_party', 'data' => $this->records()];
    }

    /** @return array<int, array<string, mixed>> */
    private function records(): array
    {
        return [$this->record('2640730', 'Amazon')];
    }

    /** @return array<string, mixed> */
    private function record(string $id, string $name, string $status = 'Registered', string $type = 'Word'): array
    {
        return [
            'application_id' => $id,
            'trademark_name' => $name,
            'status' => $status,
            'class' => '45',
            'type' => $type,
            'proprietor' => 'Example Owner',
            'description' => 'Example services',
        ];
    }

    /** @return array<string, mixed> */
    private function geminiResponse(string $summary = 'The supplied search result has substantial earlier-mark conflict evidence.'): array
    {
        $output = [
            'summary' => $summary,
            'reasons' => [[
                'title' => 'Exact name evidence',
                'detail' => 'The supplied result includes an exact active Word mark.',
                'impact' => 'negative',
            ]],
            'warnings' => [[
                'title' => 'Search estimate only',
                'detail' => 'The supplied result is an automated search estimate.',
                'action' => 'Consider a professional review before filing.',
            ]],
        ];

        return ['candidates' => [['content' => ['parts' => [['text' => json_encode($output)]]]]]];
    }
}
