<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\CareerJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = CareerJob::query()
            ->published()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search')->trim();
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('workplace'), fn ($query) => $query->where('workplace_type', $request->string('workplace')))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        $workplaceTypes = CareerJob::query()
            ->published()
            ->distinct()
            ->orderBy('workplace_type')
            ->pluck('workplace_type');

        $openJobsCount = CareerJob::query()->published()->count();

        return view('careers.index', compact('jobs', 'workplaceTypes', 'openJobsCount'));
    }

    public function show(CareerJob $careerJob): View
    {
        abort_unless($careerJob->is_open, 404);

        return view('careers.show', ['job' => $careerJob]);
    }

    public function apply(CareerJob $careerJob): View
    {
        abort_unless($careerJob->is_open, 404);

        return view('careers.apply', ['job' => $careerJob]);
    }

    public function submit(Request $request, CareerJob $careerJob): RedirectResponse
    {
        abort_unless($careerJob->is_open, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:30'],
            'current_location' => ['nullable', 'string', 'max:180'],
            'years_experience' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:500'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:500'],
            'cover_letter' => ['required', 'string', 'min:50', 'max:10000'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'website' => ['nullable', 'max:0'],
            'privacy_consent' => ['accepted'],
        ]);

        $resume = $request->file('resume');
        $path = $resume->store("career-applications/{$careerJob->id}", 'local');

        try {
            CareerApplication::create([
                ...collect($validated)->except(['resume', 'website', 'privacy_consent'])->all(),
                'career_job_id' => $careerJob->id,
                'resume_path' => $path,
                'resume_original_name' => $resume->getClientOriginalName(),
                'status' => 'new',
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('careers.apply', $careerJob)
            ->with('success', 'Your application has been submitted successfully. Thank you for your interest in joining Legal Bruz.');
    }
}
