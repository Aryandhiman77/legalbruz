@extends('layouts.app')
@section('title', 'Apply for ' . $job->title . ' | Legal Bruz Careers')
@section('meta_description', 'Submit your application for the ' . $job->title . ' role at Legal Bruz.')
@section('canonical_url', route('careers.apply', $job))
@section('meta_robots', 'noindex, follow')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/careers.css') }}">

    <div class="careers-page">
        <header class="careers-hero">
            <div class="careers-container career-detail-hero">
                <nav class="career-breadcrumb"><a href="{{ route('careers.index') }}">Careers</a> / <a href="{{ route('careers.show', $job) }}">{{ $job->title }}</a> / Apply</nav>
                <span class="careers-eyebrow">Application form</span>
                <h1>Apply for {{ $job->title }}</h1>
                <div class="career-detail-meta">
                    <span><i class="bi bi-geo-alt me-1"></i>{{ $job->location }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $job->employment_type }}</span>
                    <span><i class="bi bi-building me-1"></i>{{ $job->workplace_type }}</span>
                </div>
            </div>
        </header>

        <div class="careers-container career-apply-layout">
            <section class="career-apply-card">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif

                <h2>Tell us about yourself</h2>
                <p>Fields marked with an asterisk are required. Your résumé must be a PDF, DOC, or DOCX file up to 10 MB.</p>

                <form class="career-form" method="POST" action="{{ route('careers.submit', $job) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name">First name *</label>
                            <input id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name') }}" maxlength="100" autocomplete="given-name" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name">Last name *</label>
                            <input id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name') }}" maxlength="100" autocomplete="family-name" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email address *</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" maxlength="190" autocomplete="email" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone">Phone number *</label>
                            <input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone') }}" maxlength="30" autocomplete="tel" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label for="current_location">Current location</label>
                            <input id="current_location" name="current_location" class="form-control @error('current_location') is-invalid @enderror"
                                value="{{ old('current_location') }}" maxlength="180">
                            @error('current_location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="years_experience">Years of experience</label>
                            <input id="years_experience" name="years_experience" type="number" step="0.5" min="0" max="60"
                                class="form-control @error('years_experience') is-invalid @enderror" value="{{ old('years_experience') }}">
                            @error('years_experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="linkedin_url">LinkedIn profile</label>
                            <input id="linkedin_url" name="linkedin_url" type="url" class="form-control @error('linkedin_url') is-invalid @enderror"
                                value="{{ old('linkedin_url') }}" maxlength="500" placeholder="https://linkedin.com/in/...">
                            @error('linkedin_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="portfolio_url">Portfolio or website</label>
                            <input id="portfolio_url" name="portfolio_url" type="url" class="form-control @error('portfolio_url') is-invalid @enderror"
                                value="{{ old('portfolio_url') }}" maxlength="500" placeholder="https://...">
                            @error('portfolio_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="cover_letter">Why are you interested in this role? *</label>
                            <textarea id="cover_letter" name="cover_letter" class="form-control @error('cover_letter') is-invalid @enderror"
                                minlength="50" maxlength="10000" required>{{ old('cover_letter') }}</textarea>
                            @error('cover_letter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="resume">Résumé / CV *</label>
                            <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx"
                                class="form-control @error('resume') is-invalid @enderror" required>
                            <div class="form-text">PDF, DOC, or DOCX · maximum 10 MB</div>
                            @error('resume') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-none" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input id="privacy_consent" name="privacy_consent" value="1" type="checkbox"
                                    class="form-check-input @error('privacy_consent') is-invalid @enderror" required>
                                <label class="form-check-label fw-normal" for="privacy_consent">
                                    I consent to Legal Bruz using my information to assess this application in accordance with the
                                    <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>. *
                                </label>
                                @error('privacy_consent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="career-button" type="submit"><i class="bi bi-send"></i> Submit application</button>
                        </div>
                    </div>
                </form>
            </section>

            <aside class="career-apply-aside">
                <span class="career-job-icon"><i class="bi bi-briefcase"></i></span>
                <h3>{{ $job->title }}</h3>
                <p>{{ $job->summary }}</p>
                <div class="career-aside-row"><small>Location</small><strong>{{ $job->location }}</strong></div>
                <div class="career-aside-row"><small>Work type</small><strong>{{ $job->employment_type }} · {{ $job->workplace_type }}</strong></div>
                <a href="{{ route('careers.show', $job) }}" class="career-button career-button-outline">Review job details</a>
            </aside>
        </div>
    </div>
@endsection
