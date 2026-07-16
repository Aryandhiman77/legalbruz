@extends('layouts.app')

@section('content')
    @php
        $registryStatuses = [
            'formality_check_pass' => 'Formality Check Pass',
            'marked_for_exam' => 'Marked for Exam',
            'objected' => 'Objected',
            'hearing_pending' => 'Hearing Pending',
            'abandoned' => 'Abandoned',
            'refused' => 'Refused',
            'accepted_advertised' => 'Accepted & Advertised',
            'registered' => 'Registered',
            'no_update' => 'No Update',
        ];

        $issueOptions = [
            'status_not_moving' => 'Status not moving',
            'formality_check_pass_long_time' => 'Formality check pass for long time',
            'marked_for_exam_long_time' => 'Marked for exam for long time',
            'objection_pending' => 'Objection pending',
            'hearing_pending' => 'Hearing pending',
            'wrong_details_filed' => 'Wrong details filed',
            'no_response_previous_attorney' => 'No response from previous attorney',
            'need_attorney_change' => 'Need attorney change',
            'need_amendment_correction' => 'Need amendment/correction',
        ];
    @endphp

    <style>
        .onboarding-page {
            --onboarding-navy: #071d33;
            --onboarding-text: #26364f;
            --onboarding-muted: #68768d;
            --onboarding-teal: #008f7f;
            --onboarding-border: #dfe9ee;
            margin-top: -40px;
            padding: 34px 0 56px;
            background:
                radial-gradient(circle at 10% 10%, rgba(0, 143, 127, 0.12), transparent 260px),
                linear-gradient(180deg, #f4fbfa 0%, #ffffff 70%);
            color: var(--onboarding-text);
        }

        .onboarding-shell {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        .onboarding-stepper-section {
            margin: 0 0 32px;
            padding: 24px 30px 26px;
            border: 1px solid var(--onboarding-border);
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(7, 29, 51, 0.05);
        }

        .onboarding-stepper {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin: 0;
        }

        .onboarding-stepper::before {
            content: '';
            position: absolute;
            top: 26px;
            left: 16.67%;
            right: 16.67%;
            height: 2px;
            background: #e5e7ee;
            transform: translateY(-50%);
        }

        .onboarding-step {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            align-items: center;
            gap: 13px;
            color: var(--onboarding-navy);
            font-weight: 900;
            text-align: center;
        }

        .onboarding-step-number {
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

        .onboarding-step-label {
            min-height: 34px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: var(--onboarding-navy);
            font-size: 0.86rem;
            line-height: 1.25;
        }

        .onboarding-step.is-active,
        .onboarding-step.is-complete {
            color: var(--onboarding-teal);
        }

        .onboarding-step.is-active .onboarding-step-number,
        .onboarding-step.is-complete .onboarding-step-number {
            color: #ffffff;
            background: var(--onboarding-teal);
            border-color: rgba(0, 143, 127, 0.24);
            box-shadow: 0 16px 28px rgba(0, 143, 127, 0.2);
        }

        .onboarding-step.is-active .onboarding-step-label,
        .onboarding-step.is-complete .onboarding-step-label {
            color: var(--onboarding-teal);
        }

        .onboarding-card {
            overflow: hidden;
            border: 1px solid var(--onboarding-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 16px 38px rgba(7, 29, 51, 0.08);
        }

        .onboarding-header {
            padding: 24px 28px;
            background: linear-gradient(135deg, #071d33 0%, #008f7f 100%);
            color: #ffffff;
        }

        .onboarding-header h1 {
            margin: 0 0 6px;
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 900;
        }

        .onboarding-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.88);
            font-weight: 650;
        }

        .onboarding-body {
            padding: 26px;
        }

        .onboarding-section {
            padding: 22px;
            border: 1px solid var(--onboarding-border);
            border-radius: 12px;
            background: #fbfefd;
        }

        .onboarding-section + .onboarding-section {
            margin-top: 18px;
        }

        .onboarding-section h2 {
            margin: 0 0 18px;
            color: var(--onboarding-navy);
            font-size: 1rem;
            font-weight: 900;
        }

        .onboarding-section h2 span {
            color: var(--onboarding-teal);
        }

        .onboarding-page .form-control,
        .onboarding-page .form-select {
            min-height: 44px;
            border: 1px solid var(--onboarding-border);
            border-radius: 8px;
        }

        .issue-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .issue-option {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 46px;
            padding: 10px 12px;
            border: 1px solid var(--onboarding-border);
            border-radius: 8px;
            background: #ffffff;
            color: var(--onboarding-navy);
            font-weight: 750;
            line-height: 1.25;
        }

        .issue-option input {
            accent-color: var(--onboarding-teal);
        }

        .onboarding-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .onboarding-page .btn-primary {
            border: 0;
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
        }

        .recovery-svg {
            width: 1em;
            height: 1em;
            stroke-width: 2.2;
        }

        @media (max-width: 991px) {
            .issue-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .onboarding-shell {
                width: min(100% - 24px, 1180px);
            }

            .onboarding-stepper-section {
                margin-bottom: 22px;
                padding: 20px 18px;
            }

            .onboarding-stepper {
                grid-template-columns: 1fr;
            }

            .onboarding-stepper::before {
                display: none;
            }

            .onboarding-step {
                grid-template-columns: 46px 1fr;
                justify-items: start;
                text-align: left;
                gap: 12px;
            }

            .onboarding-step-number {
                width: 46px;
                height: 46px;
            }

            .onboarding-step-label {
                min-height: auto;
                align-items: center;
                justify-content: flex-start;
            }

            .onboarding-body,
            .onboarding-section {
                padding: 18px;
            }

            .issue-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="onboarding-page">
        <div class="onboarding-shell">
            <div class="onboarding-stepper-section">
                <div class="onboarding-stepper" aria-label="Trademark recovery steps">
                    <div class="onboarding-step is-complete">
                        <span class="onboarding-step-number">1</span>
                        <span class="onboarding-step-label">Intake Form</span>
                    </div>
                    <div class="onboarding-step is-active">
                        <span class="onboarding-step-number">2</span>
                        <span class="onboarding-step-label">Client Onboarding</span>
                    </div>
                    <div class="onboarding-step">
                        <span class="onboarding-step-number">3</span>
                        <span class="onboarding-step-label">Document Uploads</span>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please complete the highlighted onboarding fields.</strong>
                </div>
            @endif

            <div class="onboarding-card">
                <div class="onboarding-header">
                    <h1>Step 2 — Client Onboarding Flow</h1>
                    <p>Confirm applicant, trademark, previous filing, and problem details before legal review begins.</p>
                </div>

                <div class="onboarding-body">
                    <form action="{{ route('stuck-trademark.onboarding.submit', $case) }}" method="POST" data-onboarding-standalone-form>
                        @csrf

                        <div class="onboarding-section">
                            <h2><span>Section A</span> — Applicant Details</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Applicant Name</label>
                                    <input type="text" name="applicant_name" class="form-control" value="{{ old('applicant_name', $case->applicant_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $case->business_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $case->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $case->phone) }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label required">Address</label>
                                    <textarea name="applicant_address" rows="3" class="form-control" required>{{ old('applicant_address', $case->applicant_address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="onboarding-section">
                            <h2><span>Section B</span> — Trademark Details</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Trademark Name</label>
                                    <input type="text" name="trademark_name" class="form-control" value="{{ old('trademark_name', $case->trademark_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Application Number</label>
                                    <input type="text" name="application_number" class="form-control" value="{{ old('application_number', $case->application_number) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Class</label>
                                    <input type="text" name="trademark_class" class="form-control" value="{{ old('trademark_class', $case->trademark_class) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Filing Date</label>
                                    <input type="date" name="filing_date" class="form-control" value="{{ old('filing_date', optional($case->filing_date)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Current Status</label>
                                    <select name="registry_status" class="form-select">
                                        <option value="">Select current status</option>
                                        @foreach ($registryStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('registry_status', $case->registry_status) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="onboarding-section">
                            <h2><span>Section C</span> — Previous Filing Details</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Previous Attorney/Agent Name</label>
                                    <input type="text" name="prior_attorney_name" class="form-control" value="{{ old('prior_attorney_name', $case->prior_attorney_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Was TM filed personally or through professional?</label>
                                    <select name="filing_channel" class="form-select" required>
                                        <option value="">Select filing mode</option>
                                        <option value="personally" @selected(old('filing_channel', $case->filing_channel) === 'personally')>Filed personally</option>
                                        <option value="professional" @selected(old('filing_channel', $case->filing_channel) === 'professional')>Through professional</option>
                                        <option value="not_sure" @selected(old('filing_channel', $case->filing_channel) === 'not_sure')>Not sure</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Have you received any notices?</label>
                                    <select name="received_notices" class="form-select" required>
                                        <option value="">Select answer</option>
                                        <option value="yes" @selected(old('received_notices', $case->received_notices) === 'yes')>Yes</option>
                                        <option value="no" @selected(old('received_notices', $case->received_notices) === 'no')>No</option>
                                        <option value="not_sure" @selected(old('received_notices', $case->received_notices) === 'not_sure')>Not sure</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Have any replies been filed earlier?</label>
                                    <select name="replies_filed_earlier" class="form-select" required>
                                        <option value="">Select answer</option>
                                        <option value="yes" @selected(old('replies_filed_earlier', $case->replies_filed_earlier) === 'yes')>Yes</option>
                                        <option value="no" @selected(old('replies_filed_earlier', $case->replies_filed_earlier) === 'no')>No</option>
                                        <option value="not_sure" @selected(old('replies_filed_earlier', $case->replies_filed_earlier) === 'not_sure')>Not sure</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="onboarding-section">
                            <h2><span>Section D</span> — Problem Identification</h2>
                            <label class="form-label required">What issue are you facing?</label>
                            <div class="issue-grid">
                                @foreach ($issueOptions as $value => $label)
                                    <label class="issue-option">
                                        <input
                                            type="checkbox"
                                            name="onboarding_issue_types[]"
                                            value="{{ $value }}"
                                            @checked(in_array($value, old('onboarding_issue_types', $case->onboarding_issue_types ?? []), true))
                                        >
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="onboarding-actions">
                            <button class="btn btn-primary btn-lg" type="submit">
                                Complete Onboarding <x-lucide-arrow-right class="recovery-svg ms-2" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const onboardingForm = document.querySelector('[data-onboarding-standalone-form]');

            if (!onboardingForm) {
                return;
            }

            onboardingForm.addEventListener('submit', (event) => {
                if (onboardingForm.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                onboardingForm.dataset.submitting = 'true';
                onboardingForm.querySelectorAll('button[type="submit"]').forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Saving Onboarding...';
                });
            });
        });
    </script>
@endsection
