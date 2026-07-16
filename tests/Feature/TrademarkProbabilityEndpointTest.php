<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrademarkProbabilityEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.enabled' => false]);
    }

    public function test_valid_request_returns_required_json_structure_without_authentication(): void
    {
        $response = $this->postJson(route('trademark.ai-probability'), $this->validPayload());

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'analysis' => [
                    'keyword', 'analysis_mode', 'analysis_quality', 'class_required',
                    'requested_class', 'proposed_description', 'registration_probability', 'conflict_risk',
                    'risk_level', 'total_unique_marks', 'exact_registered_word_marks',
                    'exact_registered_device_marks', 'similar_registered_marks',
                    'same_class_registered_marks', 'opposed_or_objected_marks', 'active_marks',
                    'inactive_marks', 'hard_conflict', 'hard_conflict_reason',
                    'exact_same_class_word_marks', 'exact_same_class_device_marks',
                    'highest_name_similarity', 'highest_description_similarity',
                    'factors', 'reasons', 'warnings', 'disclaimer',
                    'ai_insights' => ['generated_by', 'summary', 'reasons', 'warnings'],
                ],
            ])
            ->assertJsonPath('success', true);
    }

    public function test_preliminary_response_has_no_numeric_probability_or_risk(): void
    {
        $this->postJson(route('trademark.ai-probability'), $this->validPayload())
            ->assertOk()
            ->assertJsonPath('analysis.analysis_mode', 'preliminary')
            ->assertJsonPath('analysis.class_required', true)
            ->assertJsonPath('analysis.registration_probability', null)
            ->assertJsonPath('analysis.conflict_risk', null)
            ->assertJsonPath('analysis.risk_level', 'Class Required')
            ->assertJsonPath('analysis.same_class_registered_marks', null)
            ->assertJsonPath('analysis.ai_insights.summary', 'Select the proposed trademark class to calculate a class-specific registration estimate.')
            ->assertJsonPath('analysis.ai_insights.warnings.0.title', 'Trademark class required');
    }

    public function test_proposed_description_is_limited_to_two_thousand_characters(): void
    {
        $payload = $this->validPayload();
        $payload['proposed_description'] = str_repeat('x', 2001);

        $this->postJson(route('trademark.ai-probability'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('proposed_description');
    }

    public function test_frontend_has_preliminary_display_and_hides_probability_chart(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertStringContainsString("isPreliminary ? 'Select class'", $source);
        $this->assertStringContainsString("isPreliminary ? 'Not calculated'", $source);
        $this->assertStringContainsString("tmEls['tm-probability-overview-card'].hidden = isPreliminary", $source);
        $this->assertStringContainsString("is-class-required", file_get_contents(public_path('css/home.css')));
        $this->assertStringNotContainsString("isPreliminary ? 'Low'", $source);
    }

    public function test_invalid_request_returns_422(): void
    {
        $this->postJson(route('trademark.ai-probability'), ['keyword' => 123, 'data' => 'bad'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['keyword', 'data']);
    }

    public function test_empty_data_is_rejected(): void
    {
        $this->postJson(route('trademark.ai-probability'), ['keyword' => 'Amazon', 'data' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data');
    }

    public function test_more_than_100_records_is_rejected(): void
    {
        $payload = $this->validPayload();
        $payload['data'] = array_fill(0, 101, $payload['data'][0]);

        $this->postJson(route('trademark.ai-probability'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data');
    }

    public function test_route_uses_web_csrf_behavior_without_requiring_authentication(): void
    {
        $route = app('router')->getRoutes()->getByName('trademark.ai-probability');

        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('throttle:20,1', $route->gatherMiddleware());
        $this->assertNotContains('auth', $route->gatherMiddleware());
    }

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'keyword' => 'Amazon',
            'class' => null,
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
