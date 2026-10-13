<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMatterOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_review_shows_editable_matter_overview_with_dropdown_fields(): void
    {
        [$admin, $application] = $this->makeApplication();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.review-application', $application->id))
            ->assertOk()
            ->assertSee('data-matter-edit', false)
            ->assertSee('name="type_of_applicant"', false)
            ->assertSee('<select id="matter-applicant-type"', false)
            ->assertSee('<option value="llp"', false)
            ->assertSee('<option value="startup"', false)
            ->assertSee('value="Original Applicant"', false)
            ->assertDontSee('Recommended Classes')
            ->assertDontSee('name="classes[]"', false)
            ->assertSee('Save Changes');
    }

    public function test_admin_can_update_the_matter_overview_and_duplicated_submission_details(): void
    {
        [$admin, $application] = $this->makeApplication();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.review-application', $application->id))
            ->patch(route('admin.application.matter-overview.update', $application->id), [
                'mark_brand' => 'Updated Mark',
                'applicant_name' => 'Updated Applicant',
                'type_of_applicant' => 'llp',
                'applicant_email' => 'updated@example.com',
                'applicant_phone' => '9123456780',
                'goods_services' => 'Updated legal technology services',
            ]);

        $response
            ->assertRedirect(route('admin.review-application', $application->id))
            ->assertSessionHas('success', 'Matter overview updated successfully.');

        $application->refresh();

        $this->assertSame('Updated Mark', $application->brand_name);
        $this->assertSame('Updated Applicant', $application->applicant_name);
        $this->assertSame('company', $application->entity_type);
        $this->assertSame('updated@example.com', $application->email);
        $this->assertSame('9123456780', $application->phone);
        $this->assertSame('Updated legal technology services', $application->goods_services);
        $this->assertSame([35], $application->classes);
        $this->assertSame('Updated Applicant', data_get($application->members_details, 'trademark_applicant_details.applicant_name'));
        $this->assertSame('llp', data_get($application->members_details, 'trademark_applicant_details.type_of_applicant'));
        $this->assertSame('Updated Mark', data_get($application->members_details, 'trademark_details.mark_brand_in_words'));
        $this->assertSame('Updated legal technology services', data_get($application->members_details, 'trademark_details.goods_or_services'));
    }

    public function test_matter_overview_update_rejects_invalid_dropdown_values(): void
    {
        [$admin, $application] = $this->makeApplication();

        $this->actingAs($admin, 'admin')
            ->from(route('admin.review-application', $application->id))
            ->patch(route('admin.application.matter-overview.update', $application->id), [
                'mark_brand' => 'Mark',
                'applicant_name' => 'Applicant',
                'type_of_applicant' => 'partnership',
                'applicant_email' => 'valid@example.com',
                'applicant_phone' => '9876543210',
                'goods_services' => 'Valid services',
            ])
            ->assertRedirect(route('admin.review-application', $application->id))
            ->assertSessionHasErrors(['type_of_applicant'], null, 'matterOverview');

        $this->assertSame('individual', $application->fresh()->entity_type);
    }

    public function test_matter_overview_update_requires_admin_authentication(): void
    {
        [, $application] = $this->makeApplication();

        $this->patch(route('admin.application.matter-overview.update', $application->id), [])
            ->assertRedirect(route('admin.login'));
    }

    private function makeApplication(): array
    {
        $admin = Admin::create([
            'name' => 'Matter Admin',
            'email' => 'matter-admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $client = User::factory()->create([
            'email' => 'client@example.com',
        ]);
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => 'client@example.com',
            'phone' => '9876543210',
            'email' => 'client@example.com',
            'brand_name' => 'Original Mark',
            'goods_services' => 'Original services',
            'classes' => [35],
            'status' => 'pending_admin',
            'members_details' => [
                'trademark_applicant_details' => [
                    'applicant_name' => 'Original Applicant',
                    'phone_mobile_number' => '9876543210',
                    'email_id' => 'client@example.com',
                    'type_of_applicant' => 'individual',
                ],
                'trademark_details' => [
                    'mark_brand_in_words' => 'Original Mark',
                    'goods_or_services' => 'Original services',
                ],
            ],
        ]);

        return [$admin, $application];
    }
}
