<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\WebsiteVisitor;
use App\Models\WebsiteServiceVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteVisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_views_create_one_unique_website_visitor(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertCookie('legal_bruz_visitor');

        $this->get(route('faq'))->assertOk();

        $this->assertDatabaseCount('website_visitors', 1);
        $this->assertSame(2, WebsiteVisitor::firstOrFail()->page_views);
    }

    public function test_opening_a_service_marks_the_unique_visitor_as_a_service_lead(): void
    {
        $this->get(route('landing'))->assertOk();
        $this->get(route('stuck-trademark.landing'))->assertOk();
        $this->get(route('trademark.opposition-management'))->assertOk();

        $visitor = WebsiteVisitor::firstOrFail();

        $this->assertNotNull($visitor->service_first_visited_at);
        $this->assertSame('filed_stuck_recovery', $visitor->first_service);
        $this->assertSame(1, WebsiteVisitor::whereNotNull('service_first_visited_at')->count());
        $this->assertDatabaseCount('website_service_visits', 2);
        $this->assertDatabaseHas('website_service_visits', [
            'service_key' => 'filed_stuck_recovery',
        ]);
        $this->assertDatabaseHas('website_service_visits', [
            'service_key' => 'opposition_management',
        ]);
    }

    public function test_repeat_views_of_one_service_increment_views_without_duplicating_its_visitor(): void
    {
        $this->get(route('examination-reply.landing'))->assertOk();
        $this->get(route('examination-reply.landing'))->assertOk();

        $this->assertDatabaseCount('website_service_visits', 1);
        $this->assertSame(2, WebsiteServiceVisit::firstOrFail()->page_views);
    }

    public function test_admin_pages_are_not_counted_and_dashboard_displays_visitor_totals(): void
    {
        $this->get(route('landing'))->assertOk();
        $this->get(route('examination-reply.landing'))->assertOk();

        $admin = Admin::create([
            'name' => 'Analytics Admin',
            'email' => 'analytics-admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Website visitors')
            ->assertSee('Service leads')
            ->assertSee('Visitors by service')
            ->assertSee('Examination Report Reply');

        $this->assertDatabaseCount('website_visitors', 1);
    }
}
