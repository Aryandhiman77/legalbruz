@extends('layouts.app-modern')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg overflow-hidden confirmation-shell">
                    <div class="confirmation-hero text-center text-white px-4 px-lg-5 py-5">
                        <div class="confirmation-icon mx-auto mb-4">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <span class="confirmation-kicker">Application Submitted</span>
                        <h1 class="display-6 fw-bold mt-3 mb-3">Your trademark application has been sent for administrator review.</h1>
                        <p class="lead mb-0 text-white-50">
                            Our team will review your submission and verify the application details before requesting your signatures.
                        </p>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        <div class="alert border-0 review-alert mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-envelope-paper review-alert-icon"></i>
                                <div>
                                    <h2 class="h5 fw-bold mb-2 text-dark">What happens next?</h2>
                                    <p class="mb-0 text-muted">
                                        After the administrator completes the review, you will be notified through email to submit your signatures for the next step of the process.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="status-card h-100">
                                    <div class="status-step">1</div>
                                    <h3 class="h6 fw-bold mb-2">Admin Review</h3>
                                    <p class="text-muted mb-0 small">
                                        Your application is now in the administrator review queue.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-card h-100">
                                    <div class="status-step">2</div>
                                    <h3 class="h6 fw-bold mb-2">Email Notification</h3>
                                    <p class="text-muted mb-0 small">
                                        We will send you an email once the review is complete.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-card h-100">
                                    <div class="status-step">3</div>
                                    <h3 class="h6 fw-bold mb-2">Submit Signatures</h3>
                                    <p class="text-muted mb-0 small">
                                        Follow the emailed instructions to submit your signatures.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="info-panel mb-4">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle info-panel-icon"></i>
                                <div>
                                    <h3 class="h6 fw-bold mb-2">Need to check progress?</h3>
                                    <p class="text-muted mb-0">
                                        You can track your application from the dashboard at any time. We will also notify you by email when action is required from your side.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('trademark.status', $applicationId) }}" class="btn btn-lg w-100 primary-action">
                                    <i class="bi bi-hourglass-split me-2"></i> Track Application Status
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('dashboard') }}" class="btn btn-lg w-100 btn-outline-secondary secondary-action">
                                    <i class="bi bi-house me-2"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .confirmation-shell {
            border-radius: 28px;
        }

        .confirmation-hero {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 35%),
                linear-gradient(135deg, #0f3d3e 0%, #1d6f67 52%, #2a9d8f 100%);
        }

        .confirmation-icon {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 2.8rem;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.14);
        }

        .confirmation-kicker {
            display: inline-block;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.78rem;
            font-weight: 700;
            opacity: 0.85;
        }

        .review-alert,
        .info-panel,
        .status-card {
            border-radius: 20px;
            padding: 1.25rem;
        }

        .review-alert {
            background: linear-gradient(135deg, rgba(42, 157, 143, 0.14), rgba(42, 157, 143, 0.05));
        }

        .info-panel {
            background: linear-gradient(135deg, rgba(15, 61, 62, 0.08), rgba(15, 61, 62, 0.03));
        }

        .review-alert-icon,
        .info-panel-icon {
            font-size: 1.5rem;
            color: var(--emerald);
            margin-right: 0.9rem;
            margin-top: 0.1rem;
        }

        .status-card {
            border: 1px solid rgba(15, 61, 62, 0.08);
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 61, 62, 0.06);
        }

        .status-step {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin-bottom: 1rem;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #1d6f67 0%, #2a9d8f 100%);
        }

        .primary-action {
            padding: 0.95rem 1.2rem;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #1d6f67 0%, #2a9d8f 100%);
            box-shadow: 0 14px 28px rgba(42, 157, 143, 0.22);
        }

        .secondary-action {
            padding: 0.95rem 1.2rem;
            border-radius: 14px;
            font-weight: 700;
        }
    </style>
@endsection
