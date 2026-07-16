@extends('layouts.app')

@section('content')
    @php
        $caseTypeIcons = [
            ['Objected applications', 'clipboard-check'],
            ['No registry movement', 'file-chart-column-increasing'],
            ['Hearing delays', 'mdi:gavel'],
            ['Missed hearing', 'calendar-x'],
            ['Attorney negligence', 'user-x'],
            ['Wrong filing details', 'file-warning'],
            ['Formalities issues', 'file-search'],
            ['Abandoned matters', 'briefcase'],
        ];

        $workflowIcons = [
            ['Intake & Onboarding', 'Submit registry and case details.', 'file-pen', '01'],
            ['Documents', 'Upload filing, objection, hearing, and attorney records.', 'cloud-upload', '02'],
            ['Audit', 'Legal expert diagnoses delay, defects, and recovery options.', 'search-check', '03'],
            ['Execution', 'Approve RTI, amendment, hearing, follow-up, or monitoring package.', 'shield-check', '04'],
        ];
    @endphp

    <style>
        .recovery-page {
            --recovery-navy: #071d33;
            --recovery-text: #26364f;
            --recovery-muted: #68768d;
            --recovery-teal: #008f7f;
            --recovery-teal-dark: #007669;
            --recovery-mint: #e4f5f2;
            --recovery-border: #dfe9ee;
            --recovery-panel: rgba(255, 255, 255, 0.88);
            --recovery-form-width: min(1180px, 100%);
            margin-top: -40px;
            padding: 28px 0 56px;
            background:
                radial-gradient(circle at 8% 12%, rgba(0, 143, 127, 0.12), transparent 260px),
                radial-gradient(circle at 88% 8%, rgba(0, 143, 127, 0.08), transparent 320px),
                linear-gradient(180deg, #f4fbfa 0%, #ffffff 58%, #f8fcfb 100%);
            color: var(--recovery-text);
            overflow: hidden;
        }

        .recovery-shell {
            width: min(1200px, calc(100% - 40px));
            margin: 0 auto;
        }

        .recovery-page section {
            padding: 0;
        }

        .recovery-hero {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(460px, 1.05fr);
            gap: 36px;
            align-items: center;
            min-height: 430px;
            position: relative;
        }

        .recovery-hero::before {
            content: "";
            position: absolute;
            inset: -60px 28% auto auto;
            width: 330px;
            height: 230px;
            opacity: 0.28;
            background-image: radial-gradient(circle, rgba(0, 143, 127, 0.42) 1px, transparent 1.5px);
            background-size: 16px 16px;
            pointer-events: none;
        }

        .recovery-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 8px 18px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--recovery-teal) 0%, var(--recovery-teal-dark) 100%);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(0, 143, 127, 0.18);
        }

        .recovery-badge i {
            font-size: 1rem;
        }

        .recovery-svg {
            width: 1em;
            height: 1em;
            stroke-width: 2.2;
            flex-shrink: 0;
        }

        .recovery-svg-24 {
            width: 24px;
            height: 24px;
        }

        .recovery-hero h1 {
            margin: 26px 0 14px;
            color: var(--recovery-navy);
            font-size: clamp(2.35rem, 4vw, 3.85rem);
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: 0;
        }

        .recovery-hero h1 span {
            color: var(--recovery-teal);
        }

        .recovery-accent {
            display: block;
            width: 54px;
            height: 3px;
            margin: 0 0 18px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--recovery-teal) 0%, rgba(0, 143, 127, 0) 100%);
        }

        .recovery-lead {
            max-width: 560px;
            color: var(--recovery-text);
            font-size: 1.02rem;
            line-height: 1.62;
            font-weight: 500;
        }

        .recovery-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 24px;
        }

        .recovery-primary,
        .recovery-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .recovery-primary {
            border: 1px solid var(--recovery-teal);
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
            color: #fff;
            box-shadow: 0 14px 30px rgba(0, 143, 127, 0.22);
        }

        .recovery-primary:hover,
        .recovery-secondary:hover {
            transform: translateY(-2px);
        }

        .recovery-secondary {
            border: 1px solid var(--recovery-teal);
            background: rgba(255, 255, 255, 0.75);
            color: var(--recovery-teal-dark);
        }

        .recovery-trust {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 34px;
        }

        .trust-pill {
            display: grid;
            grid-template-columns: 44px 1fr;
            gap: 10px;
            align-items: center;
            min-width: 0;
        }

        .trust-pill + .trust-pill {
            border-left: 1px solid var(--recovery-border);
            padding-left: 14px;
        }

        .trust-icon,
        .case-icon,
        .workflow-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--recovery-mint);
            color: var(--recovery-teal);
            flex-shrink: 0;
        }

        .trust-icon {
            width: 44px;
            height: 44px;
            font-size: 1.12rem;
        }

        .trust-title {
            color: var(--recovery-navy);
            font-weight: 900;
            line-height: 1.25;
            font-size: 0.9rem;
        }

        .trust-copy {
            color: var(--recovery-text);
            font-size: 0.78rem;
            line-height: 1.35;
        }

        .recovery-cases-panel {
            position: relative;
            z-index: 1;
            padding: 24px;
            border: 1px solid var(--recovery-border);
            border-radius: 14px;
            background: var(--recovery-panel);
            box-shadow: 0 16px 38px rgba(7, 29, 51, 0.09);
            backdrop-filter: blur(14px);
        }

        .cases-heading {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 16px;
            align-items: center;
            margin-bottom: 18px;
        }

        .cases-heading-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--recovery-mint);
            color: var(--recovery-teal);
            font-size: 2rem;
        }

        .cases-heading h2 {
            margin: 0;
            color: var(--recovery-navy);
            font-size: 1.28rem;
            font-weight: 900;
        }

        .case-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .case-tile {
            display: grid;
            grid-template-columns: 46px 1fr;
            gap: 12px;
            align-items: center;
            min-height: 70px;
            padding: 12px 14px;
            border: 1px solid var(--recovery-border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 12px 28px rgba(7, 29, 51, 0.04);
        }

        .case-icon {
            width: 46px;
            height: 46px;
            font-size: 1.18rem;
        }

        .case-title {
            color: var(--recovery-navy);
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .recovery-workflow {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 32px;
            margin: 28px 0 42px;
        }

        .workflow-card {
            position: relative;
            z-index: 1;
            min-height: 210px;
            padding: 22px;
            border: 1px solid var(--recovery-border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 12px 30px rgba(7, 29, 51, 0.07);
        }

        .workflow-card:not(:last-child)::after {
            content: ">";
            font-family: inherit;
            position: absolute;
            z-index: 5;
            top: 50%;
            right: -17px;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
            color: #fff;
            font-size: 1rem;
            box-shadow: 0 10px 22px rgba(0, 143, 127, 0.24);
            pointer-events: none;
        }

        .workflow-card:not(:last-child) {
            z-index: 2;
        }

        .workflow-icon {
            width: 54px;
            height: 54px;
            font-size: 1.42rem;
            margin-bottom: 16px;
        }

        .workflow-number {
            position: absolute;
            top: 22px;
            right: 22px;
            padding: 5px 9px;
            border-radius: 7px;
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
            color: #fff;
            font-weight: 900;
        }

        .workflow-card h3 {
            margin: 0 0 10px;
            color: var(--recovery-navy);
            font-size: 1.15rem;
            font-weight: 900;
        }

        .workflow-card p {
            margin: 0;
            color: var(--recovery-text);
            font-size: 0.92rem;
            line-height: 1.55;
        }

        .recovery-intake {
            width: var(--recovery-form-width);
            margin: 0 auto;
            border: 1px solid var(--recovery-border);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 16px 38px rgba(7, 29, 51, 0.08);
        }

        .intake-header {
            padding: 22px 26px;
            background: linear-gradient(135deg, #1d3557 0%, #27466d 100%);
            color: #fff;
        }

        .intake-header h2 {
            color: #fff;
            margin: 0 0 6px;
            font-size: 1.35rem;
        }

        .intake-header p {
            margin: 0;
            opacity: 0.88;
        }

        .intake-body {
            padding: 26px;
        }

        .recovery-multistep-flow {
            display: grid;
            gap: 34px;
        }

        .recovery-stepper-section {
            width: var(--recovery-form-width);
            margin: 0 auto;
            padding: 24px 30px 26px;
            border: 1px solid var(--recovery-border);
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(7, 29, 51, 0.05);
        }

        .intake-stepper {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin: 0;
        }

        .intake-stepper::before {
            content: '';
            position: absolute;
            top: 26px;
            left: 16.67%;
            right: 16.67%;
            height: 2px;
            background: #e5e7ee;
            transform: translateY(-50%);
        }

        .intake-step {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            align-items: center;
            gap: 13px;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--recovery-navy);
            font-weight: 900;
            text-align: center;
        }

        .intake-step-number {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #3f3f46;
            background: #ffffff;
            border: 3px solid #e5e7ee;
            font-size: 0.98rem;
            font-weight: 950;
        }

        .intake-step-label {
            min-height: 34px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: var(--recovery-navy);
            font-size: 0.86rem;
            line-height: 1.25;
        }

        .intake-step.is-active {
            color: var(--recovery-teal);
        }

        .intake-step.is-active .intake-step-number,
        .intake-step.is-complete .intake-step-number {
            color: #ffffff;
            background: var(--recovery-teal);
            border-color: rgba(0, 143, 127, 0.24);
            box-shadow: 0 16px 28px rgba(0, 143, 127, 0.2);
        }

        .intake-step.is-active .intake-step-label,
        .intake-step.is-complete .intake-step-label {
            color: var(--recovery-teal);
        }

        .intake-step-panel[hidden] {
            display: none !important;
        }

        .multistep-alert {
            display: none;
            margin-bottom: 16px;
        }

        .multistep-alert.is-visible {
            display: block;
        }

        .intake-section {
            padding: 22px;
            border: 1px solid var(--recovery-border);
            border-radius: 12px;
            background: #fbfefd;
        }

        .intake-section + .intake-section {
            margin-top: 18px;
        }

        .intake-section-title {
            margin: 0 0 18px;
            color: var(--recovery-navy);
            font-size: 1rem;
            font-weight: 900;
        }

        .intake-section-title span {
            color: var(--recovery-teal);
        }

        .recovery-page .form-control,
        .recovery-page .form-select {
            min-height: 44px;
            border: 1px solid var(--recovery-border);
            border-radius: 8px;
        }

        .issue-option {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 9px 11px;
            border: 1px solid var(--recovery-border);
            border-radius: 8px;
            background: #fbfefd;
            color: var(--recovery-navy);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .issue-option input {
            accent-color: var(--recovery-teal);
        }

        .issue-option.is-disabled {
            color: var(--recovery-muted);
            background: #eef3f2;
            cursor: not-allowed;
            opacity: 0.68;
        }

        .issue-option.is-disabled input {
            cursor: not-allowed;
        }

        .file-helper-text {
            display: block;
            margin-top: 6px;
            color: var(--recovery-muted);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .document-upload-field {
            display: grid;
            grid-template-columns: minmax(180px, 0.8fr) minmax(220px, 1fr);
            gap: 14px;
            align-items: center;
            padding: 14px;
            border: 1px solid var(--recovery-border);
            border-radius: 10px;
            background: #ffffff;
        }

        .document-upload-field + .document-upload-field {
            margin-top: 12px;
        }

        .document-upload-field strong {
            color: var(--recovery-navy);
        }

        .recovery-page .btn-primary {
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
            border: 0;
        }

        @media (max-width: 1199.98px) {
            .recovery-hero {
                grid-template-columns: 1fr;
                gap: 36px;
            }

            .recovery-workflow {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .workflow-card:not(:last-child)::after {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .recovery-shell {
                width: min(100% - 24px, 1420px);
            }

            .case-grid,
            .recovery-workflow,
            .recovery-trust {
                grid-template-columns: 1fr;
            }

            .trust-pill + .trust-pill {
                border-left: 0;
                padding-left: 0;
            }

            .cases-heading {
                grid-template-columns: 1fr;
            }

            .recovery-cases-panel,
            .intake-body {
                padding: 22px;
            }

            .intake-stepper {
                grid-template-columns: 1fr;
            }

            .intake-stepper::before {
                display: none;
            }

            .intake-step {
                grid-template-columns: 46px 1fr;
                justify-items: start;
                text-align: left;
                gap: 12px;
            }

            .intake-step-number {
                width: 46px;
                height: 46px;
            }

            .intake-step-label {
                min-height: auto;
                align-items: center;
                justify-content: flex-start;
            }

            .document-upload-field {
                grid-template-columns: 1fr;
            }

            .recovery-primary,
            .recovery-secondary {
                width: 100%;
            }
        }
    </style>

    <div class="recovery-page">
        <div class="recovery-shell">
            <section class="recovery-hero">
                <div>
                    <div class="recovery-badge">
                        <x-lucide-shield-check class="recovery-svg" />
                        <span>Trademark Recovery</span>
                    </div>

                    <h1>Stuck / Delayed Trademark <span>Recovery</span></h1>
                    <span class="recovery-accent"></span>
                    <p class="recovery-lead">
                        Recover delayed, objected, abandoned, hearing-pending, or improperly filed trademark applications
                        with a structured legal audit and execution workflow.
                    </p>

                    <div class="recovery-actions">
                        <a href="#intake" class="recovery-primary">
                            <x-lucide-rocket class="recovery-svg" />
                            <span>Start Recovery Audit</span>
                            <x-lucide-arrow-right class="recovery-svg" />
                        </a>
                        <a href="#workflow" class="recovery-secondary">
                            <x-lucide-git-branch class="recovery-svg" />
                            <span>View Workflow</span>
                        </a>
                    </div>

                    <div class="recovery-trust">
                        <div class="trust-pill">
                            <span class="trust-icon"><x-lucide-shield-check class="recovery-svg" /></span>
                            <div>
                                <div class="trust-title">Legal Experts</div>
                                <div class="trust-copy">5+ Years Experience</div>
                            </div>
                        </div>
                        <div class="trust-pill">
                            <span class="trust-icon"><x-lucide-clock class="recovery-svg" /></span>
                            <div>
                                <div class="trust-title">Faster Resolution</div>
                                <div class="trust-copy">Structured Process</div>
                            </div>
                        </div>
                        <div class="trust-pill">
                            <span class="trust-icon"><x-lucide-lock-keyhole class="recovery-svg" /></span>
                            <div>
                                <div class="trust-title">Secure & Confidential</div>
                                <div class="trust-copy">Your Data is Protected</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="recovery-cases-panel">
                    <div class="cases-heading">
                        <div class="cases-heading-icon"><x-lucide-scale class="recovery-svg" /></div>
                        <div>
                            <h2>Built for cases involving</h2>
                            <span class="recovery-accent"></span>
                        </div>
                    </div>

                    <div class="case-grid">
                        @foreach ($caseTypeIcons as [$item, $icon])
                            <div class="case-tile">
                                <span class="case-icon">
                                    @if ($icon === 'mdi:gavel')
                                        <x-mdi-gavel class="recovery-svg-24" />
                                    @else
                                        <x-dynamic-component :component="'lucide-' . $icon" class="recovery-svg" />
                                    @endif
                                </span>
                                <div class="case-title">{{ $item }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="recovery-workflow" id="workflow">
                @foreach ($workflowIcons as [$title, $copy, $icon, $number])
                    <div class="workflow-card">
                        <span class="workflow-number">{{ $number }}</span>
                        <span class="workflow-icon">
                            <x-dynamic-component :component="'lucide-' . $icon" class="recovery-svg" />
                        </span>
                        <h3>{{ $title }}</h3>
                        <span class="recovery-accent"></span>
                        <p>{{ $copy }}</p>
                    </div>
                @endforeach
            </section>

            <section class="recovery-multistep-flow" id="intake">
                @auth
                    <div class="recovery-stepper-section">
                        <div class="intake-stepper" data-intake-stepper>
                            <div class="intake-step is-active" data-step-indicator="1">
                                <span class="intake-step-number">1</span>
                                <span class="intake-step-label">Intake Form</span>
                            </div>
                            <div class="intake-step" data-step-indicator="2">
                                <span class="intake-step-number">2</span>
                                <span class="intake-step-label">Client Onboarding</span>
                            </div>
                            <div class="intake-step" data-step-indicator="3">
                                <span class="intake-step-number">3</span>
                                <span class="intake-step-label">Document Uploads</span>
                            </div>
                        </div>
                    </div>
                @endauth

                <div class="recovery-intake">
                    <div class="intake-header">
                        <h2>Trademark Recovery Form</h2>
                        <p>Complete all 3 steps before the legal audit begins.</p>
                    </div>
                    <div class="intake-body">
                    @guest
                        <div class="alert alert-info">
                            Please log in or create an account to submit a stuck trademark recovery case.
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary">Create Account</a>
                    @else
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please fix the highlighted fields.</strong>
                            </div>
                        @endif

                        <div class="alert multistep-alert" data-multistep-alert></div>

                        <form action="{{ route('stuck-trademark.store') }}" method="POST" enctype="multipart/form-data" data-recovery-intake-form data-step-panel="1">
                            @csrf

                            <input type="hidden" name="email" value="{{ old('email', Auth::user()->email) }}">
                            <input type="hidden" name="phone" value="{{ old('phone', Auth::user()->phone ?? 'Not provided') }}">

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section A</span> — Basic Details</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Applicant Name</label>
                                        <input type="text" name="applicant_name" class="form-control" value="{{ old('applicant_name', Auth::user()->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Application Number</label>
                                        <input type="text" name="application_number" class="form-control" value="{{ old('application_number') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Brand Name</label>
                                        <input type="text" name="trademark_name" class="form-control" value="{{ old('trademark_name') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label required">Class</label>
                                        <input type="text" name="trademark_class" class="form-control" value="{{ old('trademark_class') }}" placeholder="e.g., Class 9" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Filing Date</label>
                                        <input type="date" name="filing_date" class="form-control" value="{{ old('filing_date') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section B</span> — Current Status</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Registry Status</label>
                                        <select name="registry_status" class="form-select">
                                            <option value="">Select registry status</option>
                                            @foreach ([
                                                'formality_check_pass' => 'Formality Check Pass',
                                                'marked_for_exam' => 'Marked for Exam',
                                                'objected' => 'Objected',
                                                'hearing_pending' => 'Hearing Pending',
                                                'abandoned' => 'Abandoned',
                                                'refused' => 'Refused',
                                                'accepted_advertised' => 'Accepted & Advertised',
                                                'registered' => 'Registered',
                                                'no_update' => 'No Update',
                                            ] as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}" @selected(old('registry_status') === $statusValue)>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Screenshot of current trademark status</label>
                                        <input type="file" name="status_screenshot" class="form-control" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg">
                                        <small class="file-helper-text">Supported formats: PDF, PNG, JPG (Max size: 10MB per file).</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Previous attorney details</label>
                                        <textarea name="previous_attorney_details" rows="4" class="form-control" placeholder="Name, firm, contact details, and last update shared">{{ old('previous_attorney_details') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Any notices received</label>
                                        <textarea name="notices_received" rows="4" class="form-control" placeholder="Mention objection reports, registry notices, or emails received">{{ old('notices_received') }}</textarea>
                                        <input type="file" name="notice_documents[]" class="form-control mt-2" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple>
                                        <small class="file-helper-text">Attach notice copies if available. PDF, PNG, JPG only.</small>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Any hearing notices missed</label>
                                        <textarea name="hearing_notices_missed" rows="3" class="form-control" placeholder="Mention hearing date, missed deadline, or registry remarks">{{ old('hearing_notices_missed') }}</textarea>
                                        <input type="file" name="hearing_notice_documents[]" class="form-control mt-2" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple>
                                        <small class="file-helper-text">Attach hearing notices if available. PDF, PNG, JPG only.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section C</span> — Problem Discovery Questions</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Since when has the status remained unchanged?</label>
                                        <input type="text" name="status_unchanged_since" class="form-control" value="{{ old('status_unchanged_since') }}" placeholder="e.g., Since March 2025" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Has any objection/hearing notice been received?</label>
                                        <select name="objection_or_hearing_notice_received" class="form-select" required>
                                            <option value="">Select answer</option>
                                            <option value="yes" @selected(old('objection_or_hearing_notice_received') === 'yes')>Yes</option>
                                            <option value="no" @selected(old('objection_or_hearing_notice_received') === 'no')>No</option>
                                            <option value="not_sure" @selected(old('objection_or_hearing_notice_received') === 'not_sure')>Not sure</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Did your previous attorney explain the delay?</label>
                                        <select name="previous_attorney_explained_delay" class="form-select" required>
                                            <option value="">Select answer</option>
                                            <option value="yes" @selected(old('previous_attorney_explained_delay') === 'yes')>Yes</option>
                                            <option value="no" @selected(old('previous_attorney_explained_delay') === 'no')>No</option>
                                            <option value="not_sure" @selected(old('previous_attorney_explained_delay') === 'not_sure')>Not sure</option>
                                            <option value="not_applicable" @selected(old('previous_attorney_explained_delay') === 'not_applicable')>Not applicable</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Were you informed about any correction requirement?</label>
                                        <select name="correction_requirement_informed" class="form-select" required>
                                            <option value="">Select answer</option>
                                            <option value="yes" @selected(old('correction_requirement_informed') === 'yes')>Yes</option>
                                            <option value="no" @selected(old('correction_requirement_informed') === 'no')>No</option>
                                            <option value="not_sure" @selected(old('correction_requirement_informed') === 'not_sure')>Not sure</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Additional context</label>
                                        <textarea name="problem_summary" rows="4" class="form-control" placeholder="Add any other important facts, deadlines, or registry remarks">{{ old('problem_summary') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <x-lucide-arrow-right class="recovery-svg me-2" />Continue to Client Onboarding
                                </button>
                            </div>
                        </form>

                        <form action="#" method="POST" data-step-panel="2" data-client-onboarding-form hidden>
                            @csrf

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section A</span> — Applicant Details</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Applicant Name</label>
                                        <input type="text" name="applicant_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Business Name</label>
                                        <input type="text" name="business_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ Auth::user()->phone ?? '' }}" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label required">Address</label>
                                        <textarea name="applicant_address" rows="3" class="form-control" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section B</span> — Trademark Details</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Trademark Name</label>
                                        <input type="text" name="trademark_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Application Number</label>
                                        <input type="text" name="application_number" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Class</label>
                                        <input type="text" name="trademark_class" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Filing Date</label>
                                        <input type="date" name="filing_date" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Current Status</label>
                                        <select name="registry_status" class="form-select">
                                            <option value="">Select current status</option>
                                            @foreach ([
                                                'formality_check_pass' => 'Formality Check Pass',
                                                'marked_for_exam' => 'Marked for Exam',
                                                'objected' => 'Objected',
                                                'hearing_pending' => 'Hearing Pending',
                                                'abandoned' => 'Abandoned',
                                                'refused' => 'Refused',
                                                'accepted_advertised' => 'Accepted & Advertised',
                                                'registered' => 'Registered',
                                                'no_update' => 'No Update',
                                            ] as $statusValue => $statusLabel)
                                                <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section C</span> — Previous Filing Details</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Previous Attorney/Agent Name</label>
                                        <input type="text" name="prior_attorney_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Was TM filed personally or through professional?</label>
                                        <select name="filing_channel" class="form-select" required>
                                            <option value="">Select filing mode</option>
                                            <option value="personally">Filed personally</option>
                                            <option value="professional">Through professional</option>
                                            <option value="not_sure">Not sure</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Have you received any notices?</label>
                                        <select name="received_notices" class="form-select" required>
                                            <option value="">Select answer</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                            <option value="not_sure">Not sure</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required">Have any replies been filed earlier?</label>
                                        <select name="replies_filed_earlier" class="form-select" required>
                                            <option value="">Select answer</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                            <option value="not_sure">Not sure</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Section D</span> — Problem Identification</h3>
                                <label class="form-label required">What issue are you facing?</label>
                                <div class="row g-2">
                                    @foreach ([
                                        'status_not_moving' => 'Status not moving',
                                        'formality_check_pass_long_time' => 'Formality check pass for long time',
                                        'marked_for_exam_long_time' => 'Marked for exam for long time',
                                        'objection_pending' => 'Objection pending',
                                        'hearing_pending' => 'Hearing pending',
                                        'wrong_details_filed' => 'Wrong details filed',
                                        'no_response_previous_attorney' => 'No response from previous attorney',
                                        'need_attorney_change' => 'Need attorney change',
                                        'need_amendment_correction' => 'Need amendment/correction',
                                    ] as $issueValue => $issueLabel)
                                        <div class="col-md-4 col-sm-6">
                                            <label class="issue-option">
                                                <input type="checkbox" name="onboarding_issue_types[]" value="{{ $issueValue }}">
                                                <span>{{ $issueLabel }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <x-lucide-arrow-right class="recovery-svg me-2" />Continue to Document Uploads
                                </button>
                            </div>
                        </form>

                        <form action="#" method="POST" enctype="multipart/form-data" data-step-panel="3" data-document-upload-form hidden>
                            @csrf

                            <div class="intake-section">
                                <h3 class="intake-section-title"><span>Step 3</span> — Document Uploads</h3>
                                <p class="mb-3 text-muted">Attach the documents you currently have. Core documents are required where available, and optional files can be added if they apply to your case.</p>

                                <h4 class="intake-section-title mt-3"><span>Mandatory Uploads</span> — Core Documents</h4>
                                @foreach ([
                                    'tm_acknowledgment_receipt' => 'TM acknowledgment receipt',
                                    'status_screenshot' => 'Status screenshot',
                                    'authorization_letter' => 'Authorization letter',
                                    'pan_aadhaar_gst' => 'PAN/Aadhaar/GST (if amendment may happen)',
                                ] as $documentType => $documentLabel)
                                    <label class="document-upload-field">
                                        <strong>{{ $documentLabel }}</strong>
                                        <input type="file" name="document_uploads[{{ $documentType }}]" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp">
                                    </label>
                                @endforeach

                                <h4 class="intake-section-title mt-4"><span>Optional Uploads</span></h4>
                                @foreach ([
                                    'previous_notices' => 'Previous notices',
                                    'reply_copies' => 'Reply copies',
                                    'hearing_notices' => 'Hearing notices',
                                    'user_affidavit' => 'User affidavit',
                                    'previous_attorney_communication' => 'Previous attorney communication',
                                ] as $documentType => $documentLabel)
                                    <label class="document-upload-field">
                                        <strong>{{ $documentLabel }}</strong>
                                        <input type="file" name="document_uploads[{{ $documentType }}]" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp">
                                    </label>
                                @endforeach

                                <small class="file-helper-text">Supported formats: PDF, DOC, DOCX, PNG, JPG (Max size: 10MB per file).</small>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-primary btn-lg" type="submit">
                                    <x-lucide-upload class="recovery-svg me-2" />Submit Documents
                                </button>
                            </div>
                        </form>
                    @endguest
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stepOneForm = document.querySelector('[data-recovery-intake-form]');
            const stepTwoForm = document.querySelector('[data-client-onboarding-form]');
            const stepThreeForm = document.querySelector('[data-document-upload-form]');
            const panels = Array.from(document.querySelectorAll('[data-step-panel]'));
            const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
            const alertBox = document.querySelector('[data-multistep-alert]');

            if (!stepOneForm || !stepTwoForm || !stepThreeForm) {
                return;
            }

            let activeCaseId = null;
            let showUrl = null;
            const loadingTextByStep = {
                '1': 'Saving Intake...',
                '2': 'Saving Onboarding...',
                '3': 'Submitting Documents...',
            };

            const showMessage = (message, type = 'danger') => {
                if (!alertBox) {
                    return;
                }

                alertBox.className = `alert alert-${type} multistep-alert is-visible`;
                alertBox.textContent = message;
            };

            const clearMessage = () => {
                if (!alertBox) {
                    return;
                }

                alertBox.className = 'alert multistep-alert';
                alertBox.textContent = '';
            };

            const setStep = (step) => {
                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.stepPanel !== String(step);
                });

                indicators.forEach((indicator) => {
                    const indicatorStep = Number(indicator.dataset.stepIndicator);
                    indicator.classList.toggle('is-active', indicatorStep === step);
                    indicator.classList.toggle('is-complete', indicatorStep < step);
                });

                document.getElementById('intake')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            const setFormLoading = (form, isLoading, label = 'Saving...') => {
                form.dataset.submitting = isLoading ? 'true' : 'false';

                form.querySelectorAll('button[type="submit"]').forEach((button) => {
                    if (!button.dataset.originalHtml) {
                        button.dataset.originalHtml = button.innerHTML;
                    }

                    button.disabled = isLoading;
                    button.setAttribute('aria-busy', isLoading ? 'true' : 'false');
                    button.innerHTML = isLoading
                        ? `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${label}`
                        : button.dataset.originalHtml;
                });
            };

            const submitForm = async (form, url) => {
                clearMessage();

                const response = await fetch(url || form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = payload.errors
                        ? Object.values(payload.errors).flat()[0]
                        : payload.message;
                    throw new Error(firstError || 'Please check the form and try again.');
                }

                return payload;
            };

            const copyValue = (fromForm, toForm, name) => {
                const source = fromForm.querySelector(`[name="${name}"]`);
                const target = toForm.querySelector(`[name="${name}"]`);

                if (source && target) {
                    target.value = source.value;
                }
            };

            stepOneForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (stepOneForm.dataset.submitting === 'true') {
                    return;
                }

                setFormLoading(stepOneForm, true, loadingTextByStep['1']);

                try {
                    const payload = await submitForm(stepOneForm);
                    activeCaseId = payload.case_id;
                    showUrl = payload.show_url;
                    stepTwoForm.action = payload.onboarding_url;
                    stepThreeForm.action = payload.documents_url;

                    ['applicant_name', 'trademark_name', 'application_number', 'trademark_class', 'filing_date', 'registry_status'].forEach((name) => {
                        copyValue(stepOneForm, stepTwoForm, name);
                    });

                    copyValue(stepOneForm, stepTwoForm, 'email');
                    copyValue(stepOneForm, stepTwoForm, 'phone');
                    showMessage(payload.message || 'Step 1 saved. Continue with client onboarding.', 'success');
                    setStep(2);
                } catch (error) {
                    setFormLoading(stepOneForm, false);
                    showMessage(error.message);
                }
            });

            stepTwoForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!activeCaseId || !stepTwoForm.action || stepTwoForm.action.endsWith('#')) {
                    showMessage('Please complete Step 1 before submitting onboarding.');
                    return;
                }

                if (stepTwoForm.dataset.submitting === 'true') {
                    return;
                }

                setFormLoading(stepTwoForm, true, loadingTextByStep['2']);

                try {
                    const payload = await submitForm(stepTwoForm);
                    showUrl = payload.show_url || showUrl;
                    stepThreeForm.action = payload.documents_url || stepThreeForm.action;
                    showMessage(payload.message || 'Onboarding saved. Upload documents to continue.', 'success');
                    setStep(3);
                } catch (error) {
                    setFormLoading(stepTwoForm, false);
                    showMessage(error.message);
                }
            });

            stepThreeForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const hasFile = Array.from(stepThreeForm.querySelectorAll('input[type="file"]')).some((input) => input.files.length > 0);

                if (!hasFile) {
                    showMessage('Please attach at least one document before submitting.');
                    return;
                }

                if (stepThreeForm.dataset.submitting === 'true') {
                    return;
                }

                setFormLoading(stepThreeForm, true, loadingTextByStep['3']);

                try {
                    const payload = await submitForm(stepThreeForm);
                    window.location.href = payload.show_url || showUrl;
                } catch (error) {
                    setFormLoading(stepThreeForm, false);
                    showMessage(error.message);
                }
            });
        });
    </script>
@endsection
