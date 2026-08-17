<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CareerApplication;
use App\Models\CareerJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_open_jobs_and_job_details(): void
    {
        $job = $this->createJob();

        $this->get(route('careers.index'))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('Apply now');

        $this->get(route('careers.show', $job))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('What we’re looking for');

        $this->get(route('careers.apply', $job))
            ->assertOk()
            ->assertSee('Tell us about yourself');
    }

    public function test_unpublished_or_expired_job_is_not_publicly_available(): void
    {
        $unpublished = $this->createJob(['title' => 'Private Role', 'is_active' => false]);
        $expired = $this->createJob([
            'title' => 'Expired Role',
            'application_deadline' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('careers.show', $unpublished))->assertNotFound();
        $this->get(route('careers.show', $expired))->assertNotFound();
    }

    public function test_candidate_can_submit_application_with_private_resume(): void
    {
        Storage::fake('local');
        $job = $this->createJob();

        $response = $this->post(route('careers.submit', $job), [
            'first_name' => 'Asha',
            'last_name' => 'Sharma',
            'email' => 'asha@example.com',
            'phone' => '+91 99999 99999',
            'current_location' => 'Delhi',
            'years_experience' => '2.5',
            'linkedin_url' => 'https://linkedin.com/in/asha',
            'cover_letter' => str_repeat('I am interested in this role and can contribute to the team. ', 2),
            'resume' => UploadedFile::fake()->create('asha-resume.pdf', 200, 'application/pdf'),
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('careers.apply', $job));
        $response->assertSessionHas('success');

        $application = CareerApplication::where('email', 'asha@example.com')->firstOrFail();
        $this->assertSame($job->id, $application->career_job_id);
        $this->assertSame('new', $application->status);
        Storage::disk('local')->assertExists($application->resume_path);
    }

    public function test_invalid_application_is_not_saved(): void
    {
        Storage::fake('local');
        $job = $this->createJob();

        $this->from(route('careers.apply', $job))
            ->post(route('careers.submit', $job), [
                'first_name' => '',
                'email' => 'invalid',
                'cover_letter' => 'Too short',
            ])
            ->assertRedirect(route('careers.apply', $job))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'cover_letter', 'resume', 'privacy_consent']);

        $this->assertDatabaseCount('career_applications', 0);
    }

    public function test_admin_can_manage_jobs_and_review_applications(): void
    {
        Storage::fake('local');
        $admin = Admin::create([
            'name' => 'Career Admin',
            'email' => 'careers-admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.career-jobs.store'), $this->jobPayload(['title' => 'IP Research Intern']))
            ->assertRedirect();

        $job = CareerJob::where('title', 'IP Research Intern')->firstOrFail();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.career-jobs.update', $job), $this->jobPayload(['title' => 'IP Research Associate']))
            ->assertRedirect(route('admin.career-jobs.edit', $job));

        $this->assertDatabaseHas('career_jobs', ['id' => $job->id, 'title' => 'IP Research Associate']);

        $resume = UploadedFile::fake()->create('candidate.pdf', 100, 'application/pdf');
        $path = $resume->store("career-applications/{$job->id}", 'local');
        $application = CareerApplication::create([
            'career_job_id' => $job->id,
            'first_name' => 'Ravi',
            'last_name' => 'Kumar',
            'email' => 'ravi@example.com',
            'phone' => '9999999999',
            'cover_letter' => str_repeat('Suitable candidate details. ', 3),
            'resume_path' => $path,
            'resume_original_name' => 'candidate.pdf',
            'status' => 'new',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.career-applications.show', $application))
            ->assertOk()
            ->assertSee('Ravi Kumar');

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.career-applications.update', $application), [
                'status' => 'shortlisted',
                'admin_notes' => 'Invite for the first interview.',
            ])
            ->assertRedirect(route('admin.career-applications.show', $application));

        $this->assertDatabaseHas('career_applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
            'admin_notes' => 'Invite for the first interview.',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.career-applications.resume', $application))
            ->assertOk()
            ->assertDownload('candidate.pdf');
    }

    private function createJob(array $overrides = []): CareerJob
    {
        return CareerJob::create(array_merge($this->jobPayload(), $overrides));
    }

    private function jobPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Legal Operations Associate',
            'location' => 'Ambala Cantt, Haryana',
            'employment_type' => 'Full-time',
            'workplace_type' => 'Hybrid',
            'experience_level' => '1–2 years',
            'salary_range' => null,
            'summary' => 'Support legal operations and client workflows.',
            'description' => 'Work with the legal operations team to deliver clear, accurate, and timely client services.',
            'responsibilities' => "Manage case records.\nCoordinate client updates.",
            'requirements' => "Strong communication skills.\nExcellent attention to detail.",
            'benefits' => "Learning support.\nGrowth opportunities.",
            'application_deadline' => null,
            'sort_order' => 10,
            'is_active' => '1',
        ], $overrides);
    }
}
