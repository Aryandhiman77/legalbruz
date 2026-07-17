<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrademarkProbabilityEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.enabled' => false]);
    }

    public function test_empty_data_is_valid_and_returns_complete_numeric_analysis(): void
    {
        $response = $this->postJson(route('trademark.ai-probability'), ['keyword' => 'NewBrand', 'data' => []]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('analysis.registration_probability', 95)
            ->assertJsonPath('analysis.conflict_risk', 5)
            ->assertJsonPath('analysis.risk_level', 'Low')
            ->assertJsonPath('analysis.total_unique_marks', 0)
            ->assertJsonPath('analysis.exact_active_word_marks', 0)
            ->assertJsonPath('analysis.exact_active_device_marks', 0)
            ->assertJsonPath('analysis.similar_active_marks', 0)
            ->assertJsonPath('analysis.unique_active_classes', 0)
            ->assertJsonCount(6, 'analysis.factors');
    }

    public function test_response_contains_v4_counts_factors_confidence_and_insights(): void
    {
        $this->postJson(route('trademark.ai-probability'), $this->payload())
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'analysis' => [
                    'keyword', 'registration_probability', 'conflict_risk', 'risk_level', 'confidence_score',
                    'total_unique_marks', 'exact_active_word_marks', 'exact_active_device_marks',
                    'exact_pending_word_marks', 'exact_pending_device_marks', 'very_close_active_marks',
                    'close_active_marks', 'phonetic_active_matches', 'inactive_exact_marks', 'active_marks',
                    'pending_marks', 'inactive_marks', 'unique_active_classes', 'highest_name_similarity',
                    'exact_registered_word_marks', 'exact_registered_device_marks', 'similar_active_marks',
                    'factors', 'reasons', 'warnings', 'disclaimer',
                    'ai_insights' => ['generated_by', 'summary', 'reasons', 'warnings'],
                ],
            ]);
    }

    public function test_class_and_description_are_not_required(): void
    {
        $this->postJson(route('trademark.ai-probability'), ['keyword' => 'NewBrand', 'data' => []])
            ->assertOk()
            ->assertJsonMissingValidationErrors(['requested_class', 'class', 'proposed_description'])
            ->assertJsonMissing(['risk_level' => 'More Information Required'])
            ->assertJsonMissing(['risk_level' => 'Class Required']);
    }

    public function test_invalid_core_request_returns_422(): void
    {
        $this->postJson(route('trademark.ai-probability'), ['keyword' => 123])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['keyword', 'data']);
    }

    public function test_more_than_one_hundred_records_is_rejected(): void
    {
        $payload = $this->payload();
        $payload['data'] = array_fill(0, 101, $payload['data'][0]);

        $this->postJson(route('trademark.ai-probability'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data');
    }

    public function test_search_endpoint_marks_valid_zero_result_search_successful(): void
    {
        Http::fake(['www.quickcompany.in/*' => Http::response('<html><body>No matches</body></html>', 200)]);

        $this->getJson('/scrape-trademark?keyword=NewBrand')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 0)
            ->assertJsonPath('data', []);
    }

    public function test_frontend_enables_button_for_successful_zero_results(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertStringContainsString('tmSearchState.searchCompleted', $source);
        $this->assertStringContainsString('tmSearchState.searchSucceeded', $source);
        $this->assertStringContainsString('tmSearchState.keyword.trim().length >= 2', $source);
        $this->assertStringNotContainsString('results.length === 0', $source);
        $this->assertStringNotContainsString('results.length > 0', $source);
    }

    public function test_frontend_always_recreates_both_charts(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertStringContainsString('tmProbabilityDoughnutChart?.destroy()', $source);
        $this->assertStringContainsString('tmProbabilityFactorsChart?.destroy()', $source);
        $this->assertStringContainsString("type: 'doughnut'", $source);
        $this->assertStringContainsString("type: 'bar'", $source);
        $this->assertStringContainsString("indexAxis: 'y'", $source);
        $this->assertStringContainsString('maintainAspectRatio: false', $source);
        $this->assertStringNotContainsString('charts.hidden', $source);
        $this->assertStringNotContainsString('if (isPreliminary)', $source);
    }

    public function test_probability_modal_has_only_search_term_and_required_cards(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertStringNotContainsString('id="tm-probability-class"', $source);
        $this->assertStringNotContainsString('id="tm-proposed-description"', $source);
        $this->assertStringNotContainsString('Same-class matches', $source);
        $this->assertStringNotContainsString('Not checked', $source);
        $this->assertStringContainsString('Active classes found', $source);
        $this->assertStringContainsString('id="tm-confidence-score"', $source);
    }

    public function test_modal_retains_mobile_responsive_layout(): void
    {
        $css = file_get_contents(public_path('css/home.css'));

        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('.tm-probability-counts { grid-template-columns: 1fr; }', $css);
        $this->assertStringContainsString('.tm-chart-card canvas { max-height: 270px; }', $css);
    }

    public function test_route_remains_public_throttled_and_csrf_protected(): void
    {
        $route = app('router')->getRoutes()->getByName('trademark.ai-probability');

        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('throttle:20,1', $route->gatherMiddleware());
        $this->assertNotContains('auth', $route->gatherMiddleware());
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'keyword' => 'Amazon',
            'source_type' => 'third_party',
            'data' => [[
                'application_id' => '2640730',
                'trademark_name' => 'Amazon',
                'status' => 'Registered',
                'class' => '45',
                'type' => 'Word',
                'proprietor' => 'Amazon Technologies Inc',
                'description' => 'Personal and social services',
            ]],
        ];
    }
}
