<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\CareerJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCareerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = CareerApplication::query()
            ->with('job:id,title')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('job'), fn ($query) => $query->where('career_job_id', $request->integer('job')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $jobs = CareerJob::orderBy('title')->get(['id', 'title']);

        return view('admin.careers.applications.index', compact('applications', 'jobs'));
    }

    public function show(CareerApplication $careerApplication): View
    {
        $careerApplication->load('job');

        if (! $careerApplication->reviewed_at) {
            $careerApplication->update([
                'reviewed_at' => now(),
                'status' => $careerApplication->status === 'new' ? 'reviewing' : $careerApplication->status,
            ]);
        }

        return view('admin.careers.applications.show', ['application' => $careerApplication]);
    }

    public function update(Request $request, CareerApplication $careerApplication): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewing', 'shortlisted', 'interview', 'offered', 'rejected', 'hired'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $careerApplication->update([
            ...$validated,
            'reviewed_at' => $careerApplication->reviewed_at ?? now(),
        ]);

        return redirect()
            ->route('admin.career-applications.show', $careerApplication)
            ->with('success', 'Application updated successfully.');
    }

    public function downloadResume(CareerApplication $careerApplication): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($careerApplication->resume_path), 404);

        return Storage::disk('local')->download(
            $careerApplication->resume_path,
            $careerApplication->resume_original_name
        );
    }
}
