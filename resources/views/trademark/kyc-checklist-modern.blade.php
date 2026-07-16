@extends('layouts.app-modern')

@section('content')
<style>
    .checklist-item:hover{
        border-color:#e8e8ee!important;
    }
</style>
    <div class="container">
        <!-- Progress Bar -->
        <div class="progress-section">
            <div class="step-tracker">
                <div class="step-item completed">
                    <div class="step-number">1</div>
                    <div class="step-label">Type</div>
                </div>
                <div class="step-item active">
                    <div class="step-number">2</div>
                    <div class="step-label">Required</div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-label">Form</div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-label">Payment</div>
                </div>
                <div class="step-item">
                    <div class="step-number">5</div>
                    <div class="step-label">Submit</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Required Documents - {{ ucfirst($type) }}</h2>
                        <small>Please prepare the following documents for verification</small>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4" style="font-size: 0.95rem;">
                            To complete your trademark registration, we need to verify your identity and business details.
                            Please prepare scanned copies of the documents listed below.
                        </p>

                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb"></i>
                            <strong>Tip:</strong> Take clear scans in color if possible. Blurry or unclear documents may
                            delay the verification process.
                        </div>

                        <h5 style="color: var(--navy); font-weight: 800; margin-top: 30px; margin-bottom: 20px;">
                            <i class="bi bi-checklist" style="margin-right: 10px; color: var(--emerald);"></i>
                            Required Documents
                        </h5>

                        <div class="checklist-wrapper">
                            @foreach ($requirements as $index => $req)
                                <div class="checklist-item">
                                    <div class="form-check">
                                        
                                        <label class="form-check-label" for="doc{{ $loop->index }}">
                                            <strong>{{ $req }}</strong>
                                            <small>Scanned copy required • Max 10MB per file</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-warning mt-5">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Important:</strong> All documents must be clear, valid, and in color. Black & white or
                            blurry documents may not be accepted. You'll upload these in the next step.
                        </div>

                        <div class="row mt-5 g-3">
                            <div class="col-md-6">
                                <a href="{{ route('trademark.type-selection') }}"
                                    class="btn btn-outline-secondary w-100 btn-lg">
                                    <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('trademark.application-form', $type) }}"
                                    class="btn btn-primary w-100 btn-lg">
                                    Continue<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
