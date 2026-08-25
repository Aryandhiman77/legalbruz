<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CustomerReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_only_published_reviews_in_configured_order(): void
    {
        CustomerReview::query()->delete();

        CustomerReview::create([
            'customer_name' => 'Second Customer',
            'customer_title' => 'Business Owner',
            'review' => 'This should be displayed second.',
            'rating' => 4,
            'sort_order' => 20,
            'is_active' => true,
        ]);
        CustomerReview::create([
            'customer_name' => 'First Customer',
            'customer_title' => 'Founder',
            'review' => 'This should be displayed first.',
            'rating' => 5,
            'sort_order' => 10,
            'is_active' => true,
        ]);
        CustomerReview::create([
            'customer_name' => 'Hidden Customer',
            'customer_title' => 'Director',
            'review' => 'This draft must stay hidden.',
            'rating' => 5,
            'sort_order' => 5,
            'is_active' => false,
        ]);

        $this->get(route('landing'))
            ->assertOk()
            ->assertSeeInOrder(['First Customer', 'Second Customer'])
            ->assertDontSee('Hidden Customer')
            ->assertSee('data-testimonials-carousel', false);

        $carouselCss = file_get_contents(public_path('css/home.css'));
        $this->assertStringContainsString('--reviews-per-view: 3', $carouselCss);
        $this->assertStringContainsString('--reviews-per-view: 2', $carouselCss);
        $this->assertStringContainsString('--reviews-per-view: 1', $carouselCss);
    }

    public function test_admin_can_create_update_and_delete_a_review(): void
    {
        Storage::fake('public');

        $admin = Admin::create([
            'name' => 'Review Admin',
            'email' => 'reviews-admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.reviews.store'), [
                'customer_name' => 'New Customer',
                'customer_title' => 'Founder',
                'review' => 'A clear and helpful service experience.',
                'rating' => 5,
                'sort_order' => 40,
                'is_active' => '1',
                'logo' => UploadedFile::fake()->image('customer-logo.png', 240, 240),
            ])
            ->assertRedirect();

        $review = CustomerReview::where('customer_name', 'New Customer')->firstOrFail();
        $originalLogoPath = $review->logo_path;
        Storage::disk('public')->assertExists($originalLogoPath);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.reviews.update', $review), [
                'customer_name' => 'Updated Customer',
                'customer_title' => 'Director',
                'review' => 'The updated review copy.',
                'rating' => 4,
                'sort_order' => 15,
                'is_active' => '0',
                'logo' => UploadedFile::fake()->image('updated-logo.webp', 300, 300),
            ])
            ->assertRedirect(route('admin.reviews.edit', $review));

        $this->assertDatabaseHas('customer_reviews', [
            'id' => $review->id,
            'customer_name' => 'Updated Customer',
            'rating' => 4,
            'sort_order' => 15,
            'is_active' => false,
        ]);
        $updatedLogoPath = $review->fresh()->logo_path;
        $this->assertNotSame($originalLogoPath, $updatedLogoPath);
        Storage::disk('public')->assertMissing($originalLogoPath);
        Storage::disk('public')->assertExists($updatedLogoPath);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect(route('admin.reviews.index'));

        $this->assertDatabaseMissing('customer_reviews', ['id' => $review->id]);
        Storage::disk('public')->assertMissing($updatedLogoPath);
    }
}
