<?php

namespace App\Http\Controllers;

use App\Models\CareerJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCareerJobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = CareerJob::query()
            ->withCount('applications')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status') === 'open', fn ($query) => $query->published())
            ->when($request->input('status') === 'closed', function ($query) {
                $query->where(function ($query) {
                    $query->where('is_active', false)
                        ->orWhereDate('application_deadline', '<', today());
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.careers.jobs.index', compact('jobs'));
    }

    public function create(): View
    {
        return view('admin.careers.jobs.create', [
            'job' => new CareerJob([
                'employment_type' => 'Full-time',
                'workplace_type' => 'On-site',
                'sort_order' => (CareerJob::max('sort_order') ?? 0) + 10,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['slug'] = CareerJob::uniqueSlug($data['title']);
        $job = CareerJob::create($data);

        return redirect()
            ->route('admin.career-jobs.edit', $job)
            ->with('success', 'Job role created successfully.');
    }

    public function edit(CareerJob $careerJob): View
    {
        return view('admin.careers.jobs.edit', ['job' => $careerJob]);
    }

    public function update(Request $request, CareerJob $careerJob): RedirectResponse
    {
        $data = $this->validatedData($request);
        if ($careerJob->title !== $data['title']) {
            $data['slug'] = CareerJob::uniqueSlug($data['title'], $careerJob->id);
        }

        $careerJob->update($data);

        return redirect()
            ->route('admin.career-jobs.edit', $careerJob)
            ->with('success', 'Job role updated successfully.');
    }

    public function destroy(CareerJob $careerJob): RedirectResponse
    {
        if ($careerJob->applications()->exists()) {
            return redirect()
                ->route('admin.career-jobs.index')
                ->with('error', 'This role has applications and cannot be deleted. Unpublish it instead.');
        }

        $careerJob->delete();

        return redirect()
            ->route('admin.career-jobs.index')
            ->with('success', 'Job role deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(['Full-time', 'Part-time', 'Contract', 'Internship'])],
            'workplace_type' => ['required', Rule::in(['On-site', 'Hybrid', 'Remote'])],
            'experience_level' => ['nullable', 'string', 'max:100'],
            'salary_range' => ['nullable', 'string', 'max:120'],
            'summary' => ['required', 'string', 'max:1000'],
            'description' => ['required', 'string', 'max:20000'],
            'responsibilities' => ['nullable', 'string', 'max:20000'],
            'requirements' => ['required', 'string', 'max:20000'],
            'benefits' => ['nullable', 'string', 'max:20000'],
            'application_deadline' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
