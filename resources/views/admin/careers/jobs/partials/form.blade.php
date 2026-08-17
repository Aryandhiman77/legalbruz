<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1" style="color:#1D3557;">{{ $title }}</h1>
            <p class="text-muted mb-0">Manage the role information displayed on the public careers page.</p>
        </div>
        <a href="{{ route('admin.career-jobs.index') }}" class="btn btn-outline-secondary btn-sm">Back to Job Roles</a>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST') @method($method) @endif
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-8">
                        <label for="title" class="form-label fw-bold">Job title</label>
                        <input id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $job->title) }}" maxlength="255" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="location" class="form-label fw-bold">Location</label>
                        <input id="location" name="location" class="form-control @error('location') is-invalid @enderror"
                            value="{{ old('location', $job->location) }}" maxlength="255" required>
                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="employment_type" class="form-label fw-bold">Employment type</label>
                        <select id="employment_type" name="employment_type" class="form-select" required>
                            @foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $type)
                                <option value="{{ $type }}" @selected(old('employment_type', $job->employment_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="workplace_type" class="form-label fw-bold">Workplace type</label>
                        <select id="workplace_type" name="workplace_type" class="form-select" required>
                            @foreach (['On-site', 'Hybrid', 'Remote'] as $type)
                                <option value="{{ $type }}" @selected(old('workplace_type', $job->workplace_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="experience_level" class="form-label fw-bold">Experience</label>
                        <input id="experience_level" name="experience_level" class="form-control"
                            value="{{ old('experience_level', $job->experience_level) }}" maxlength="100" placeholder="e.g. 2–4 years">
                    </div>
                    <div class="col-md-3">
                        <label for="salary_range" class="form-label fw-bold">Salary range</label>
                        <input id="salary_range" name="salary_range" class="form-control"
                            value="{{ old('salary_range', $job->salary_range) }}" maxlength="120" placeholder="Optional">
                    </div>
                    <div class="col-12">
                        <label for="summary" class="form-label fw-bold">Card summary</label>
                        <textarea id="summary" name="summary" rows="3" class="form-control @error('summary') is-invalid @enderror"
                            maxlength="1000" required>{{ old('summary', $job->summary) }}</textarea>
                        <div class="form-text">A concise introduction shown on the careers listing card.</div>
                        @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Role description</label>
                        <textarea id="description" name="description" rows="6" class="form-control @error('description') is-invalid @enderror"
                            maxlength="20000" required>{{ old('description', $job->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @foreach ([
                        'responsibilities' => ['Responsibilities', 'Enter one responsibility per line.'],
                        'requirements' => ['Requirements', 'Enter one requirement per line.'],
                        'benefits' => ['Benefits', 'Enter one benefit per line.'],
                    ] as $field => [$label, $help])
                        <div class="col-md-{{ $field === 'benefits' ? '12' : '6' }}">
                            <label for="{{ $field }}" class="form-label fw-bold">{{ $label }}</label>
                            <textarea id="{{ $field }}" name="{{ $field }}" rows="7"
                                class="form-control @error($field) is-invalid @enderror"
                                maxlength="20000" @if ($field === 'requirements') required @endif>{{ old($field, $job->{$field}) }}</textarea>
                            <div class="form-text">{{ $help }}</div>
                            @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endforeach
                    <div class="col-md-4">
                        <label for="application_deadline" class="form-label fw-bold">Application deadline</label>
                        <input id="application_deadline" name="application_deadline" type="date" class="form-control"
                            value="{{ old('application_deadline', $job->application_deadline?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="sort_order" class="form-label fw-bold">Display order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="65535" class="form-control"
                            value="{{ old('sort_order', $job->sort_order) }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2 career-publish-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input id="is_active" name="is_active" value="1" type="checkbox" class="form-check-input"
                                @checked(old('is_active', $job->is_active))>
                            <label for="is_active" class="form-check-label fw-bold">Published and accepting applications</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white p-4 d-flex justify-content-end">
                <button class="btn btn-primary" style="background:#2A9D8F;border:0;">{{ $submitLabel }}</button>
            </div>
        </div>
    </form>
</div>

<style>
    .career-publish-switch .form-check-input {
        width: 2.6rem;
        height: 1.45rem;
        cursor: pointer;
    }

    .career-publish-switch .form-check-input:checked {
        border-color: #2A9D8F;
        background-color: #2A9D8F;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Ccircle cx='10' cy='10' r='8.5' fill='white'/%3E%3Cpath d='M6.2 10.1 8.8 12.7 14 7.5' fill='none' stroke='%232A9D8F' stroke-width='2.1' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-position: right center;
        background-size: 1.35rem 1.35rem;
    }

    .career-publish-switch .form-check-input:focus {
        border-color: #2A9D8F;
        box-shadow: 0 0 0 .2rem rgba(42, 157, 143, .18);
    }

    .career-publish-switch .form-check-label {
        cursor: pointer;
    }
</style>
