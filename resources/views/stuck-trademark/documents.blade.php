@extends('layouts.app')

@section('content')
    @php
        $mandatoryDocumentTypes = [
            'tm_acknowledgment_receipt' => 'TM acknowledgment receipt',
            'status_screenshot' => 'Status screenshot',
            'authorization_letter' => 'Authorization letter',
            'pan_aadhaar_gst' => 'PAN/Aadhaar/GST (if amendment may happen)',
        ];
        $optionalDocumentTypes = [
            'previous_notices' => 'Previous notices',
            'reply_copies' => 'Reply copies',
            'hearing_notices' => 'Hearing notices',
            'user_affidavit' => 'User affidavit',
            'previous_attorney_communication' => 'Previous attorney communication',
        ];
        $applicationDocumentTypes = [
            'registry_status_screenshot' => 'Registry Status Screenshot',
            'notice_received' => 'Notice Received',
            'missed_hearing_notice' => 'Missed Hearing Notice',
        ];
        $documentTypeLabels = [...$mandatoryDocumentTypes, ...$optionalDocumentTypes, ...$applicationDocumentTypes];
        $caseDocumentTypes = array_keys([...$mandatoryDocumentTypes, ...$optionalDocumentTypes]);
        $reuploadDocumentTypes = array_keys($documentTypeLabels);
        $latestDocumentsByType = $case->documents->sortByDesc('id')->unique('document_type')->values();
        $latestCaseDocumentsByType = $latestDocumentsByType
            ->whereIn('document_type', $caseDocumentTypes)
            ->values();
        $hasCaseDocumentsForVerification = $latestCaseDocumentsByType->isNotEmpty();
        $reuploadRequestedDocuments = $latestDocumentsByType
            ->filter(fn ($document) => in_array($document->status, ['reupload_requested', 'rejected'], true))
            ->whereIn('document_type', $reuploadDocumentTypes)
            ->values();
        $documentUploadActive = ! $hasCaseDocumentsForVerification || in_array($case->status, [
            \App\Support\StuckTrademarkWorkflow::PROBLEM_IDENTIFIED,
            \App\Support\StuckTrademarkWorkflow::AWAITING_DOCUMENTS,
            \App\Support\StuckTrademarkWorkflow::REUPLOAD_REQUIRED,
        ], true);
        $documentSubmissionLocked = ! $documentUploadActive;
    @endphp

    <style>
        .documents-step-page {
            --documents-navy: #071d33;
            --documents-text: #26364f;
            --documents-muted: #68768d;
            --documents-teal: #008f7f;
            --documents-border: #dfe9ee;
            margin-top: -40px;
            padding: 34px 0 56px;
            background:
                radial-gradient(circle at 10% 10%, rgba(0, 143, 127, 0.12), transparent 260px),
                linear-gradient(180deg, #f4fbfa 0%, #ffffff 70%);
            color: var(--documents-text);
        }

        .documents-step-shell {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        .documents-stepper-section {
            margin: 0 0 32px;
            padding: 24px 30px 26px;
            border: 1px solid var(--documents-border);
            background: #ffffff;
            box-shadow: 0 10px 28px rgba(7, 29, 51, 0.05);
        }

        .documents-stepper {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .documents-stepper::before {
            content: '';
            position: absolute;
            top: 26px;
            left: 16.67%;
            right: 16.67%;
            height: 2px;
            background: #e5e7ee;
            transform: translateY(-50%);
        }

        .documents-step {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            gap: 13px;
            color: var(--documents-navy);
            font-weight: 900;
            text-align: center;
        }

        .documents-step-number {
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

        .documents-step-label {
            min-height: 34px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: var(--documents-navy);
            font-size: 0.86rem;
            line-height: 1.25;
        }

        .documents-step.is-active,
        .documents-step.is-complete {
            color: var(--documents-teal);
        }

        .documents-step.is-active .documents-step-number,
        .documents-step.is-complete .documents-step-number {
            color: #ffffff;
            background: var(--documents-teal);
            border-color: rgba(0, 143, 127, 0.24);
            box-shadow: 0 16px 28px rgba(0, 143, 127, 0.2);
        }

        .documents-step.is-active .documents-step-label,
        .documents-step.is-complete .documents-step-label {
            color: var(--documents-teal);
        }

        .documents-card {
            overflow: hidden;
            border: 1px solid var(--documents-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 16px 38px rgba(7, 29, 51, 0.08);
        }

        .documents-header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #071d33 0%, #008f7f 100%);
            color: #ffffff;
        }

        .documents-header-icon {
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.13);
            color: #ffffff;
        }

        .documents-header-icon svg {
            width: 28px;
            height: 28px;
            stroke-width: 2.4;
        }

        .documents-header h1 {
            margin: 0 0 6px;
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 900;
        }

        .documents-header p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 700;
        }

        .documents-body {
            padding: 26px;
        }

        .documents-upload-panel {
            padding: 22px;
            border: 2px dashed #cbd7e8;
            border-radius: 12px;
            background: #fbfefd;
        }

        .documents-upload-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .documents-upload-head h2 {
            margin: 0 0 8px;
            color: var(--documents-navy);
            font-size: 1.15rem;
            font-weight: 900;
        }

        .documents-upload-head p {
            margin: 0;
            color: #44546a;
            font-weight: 750;
            line-height: 1.45;
        }

        .documents-lock-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid #cbd7e8;
            border-radius: 999px;
            background: #f8fafc;
            color: #44546a;
            font-weight: 900;
            white-space: nowrap;
        }

        .documents-lock-pill svg {
            width: 18px;
            height: 18px;
        }

        .documents-group-title {
            margin: 22px 0 14px;
            color: var(--documents-teal);
            font-size: 1rem;
            font-weight: 900;
        }

        .documents-field {
            display: grid;
            grid-template-columns: minmax(220px, 0.78fr) minmax(260px, 1fr);
            gap: 18px;
            align-items: center;
            min-height: 74px;
            padding: 14px 18px;
            border: 1px solid #e6eef3;
            border-radius: 10px;
            background: #ffffff;
        }

        .documents-field + .documents-field {
            margin-top: 14px;
        }

        .documents-field strong {
            color: var(--documents-navy);
            font-size: 0.92rem;
            font-weight: 900;
            line-height: 1.35;
        }

        .documents-required {
            color: #dc2626;
            font-weight: 950;
        }

        .documents-admin-note {
            display: block;
            margin-top: 6px;
            color: #a16207;
            font-size: 0.76rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .documents-field .form-control {
            min-height: 44px;
            border: 1px solid var(--documents-border);
            border-radius: 8px;
        }

        .documents-helper {
            display: block;
            margin-top: 16px;
            color: var(--documents-muted);
            font-size: 0.82rem;
            font-weight: 800;
        }

        .documents-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .documents-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, #00a18f 0%, #007d70 100%);
            color: #ffffff;
            font-weight: 900;
        }

        .documents-submit:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .documents-submit svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.4;
        }

        @media (max-width: 576px) {
            .documents-step-shell {
                width: min(100% - 24px, 1180px);
            }

            .documents-stepper-section {
                margin-bottom: 22px;
                padding: 20px 18px;
            }

            .documents-stepper {
                grid-template-columns: 1fr;
            }

            .documents-stepper::before {
                display: none;
            }

            .documents-step {
                grid-template-columns: 46px 1fr;
                justify-items: start;
                text-align: left;
                gap: 12px;
            }

            .documents-step-number {
                width: 46px;
                height: 46px;
            }

            .documents-step-label {
                min-height: auto;
                align-items: center;
                justify-content: flex-start;
            }

            .documents-header,
            .documents-upload-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .documents-body,
            .documents-upload-panel {
                padding: 18px;
            }

            .documents-field {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .documents-actions {
                justify-content: stretch;
            }

            .documents-submit {
                width: 100%;
            }
        }
    </style>

    <div class="documents-step-page">
        <div class="documents-step-shell">
            <div class="documents-stepper-section">
                <div class="documents-stepper" aria-label="Trademark recovery steps">
                    <div class="documents-step is-complete">
                        <span class="documents-step-number">1</span>
                        <span class="documents-step-label">Intake Form</span>
                    </div>
                    <div class="documents-step is-complete">
                        <span class="documents-step-number">2</span>
                        <span class="documents-step-label">Client Onboarding</span>
                    </div>
                    <div class="documents-step is-active">
                        <span class="documents-step-number">3</span>
                        <span class="documents-step-label">Document Uploads</span>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please attach valid documents before submitting.</strong>
                </div>
            @endif

            <div class="documents-card">
                <div class="documents-header">
                    <span class="documents-header-icon"><x-lucide-file-text /></span>
                    <div>
                        <h1>Step 3 — Document Uploads</h1>
                        <p>Submit and manage your case related documents.</p>
                    </div>
                </div>

                <div class="documents-body">
                    <form action="{{ route('stuck-trademark.documents.store', $case) }}" method="POST" enctype="multipart/form-data" data-documents-step-form>
                        @csrf

                        <div class="documents-upload-panel">
                            <div class="documents-upload-head">
                                <div>
                                    <h2>{{ $reuploadRequestedDocuments->isNotEmpty() ? 'Reupload requested documents' : 'Submit case documents' }}</h2>
                                    <p>
                                        @if ($reuploadRequestedDocuments->isNotEmpty())
                                            Admin requested changes for the documents below. Attach corrected files and submit them again.
                                        @elseif ($documentSubmissionLocked)
                                            Your document submission is locked while files are being reviewed or after they have been verified.
                                        @else
                                            Upload the recovery documents you have now. After submission, admin verification is required before the audit package can be purchased.
                                        @endif
                                    </p>
                                </div>
                                @if ($documentSubmissionLocked)
                                    <span class="documents-lock-pill"><x-lucide-lock /> Locked</span>
                                @endif
                            </div>

                            @if ($reuploadRequestedDocuments->isNotEmpty())
                                @foreach ($reuploadRequestedDocuments as $document)
                                    @php
                                        $label = $documentTypeLabels[$document->document_type] ?? ucwords(str_replace('_', ' ', $document->document_type));
                                    @endphp
                                    <label class="documents-field">
                                        <span>
                                            <strong>{{ $label }}</strong>
                                            @if ($document->verification_notes)
                                                <span class="documents-admin-note">Admin note: {{ $document->verification_notes }}</span>
                                            @endif
                                        </span>
                                        <input type="file" name="document_uploads[{{ $document->document_type }}]" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp" required {{ $documentSubmissionLocked ? 'disabled' : '' }}>
                                    </label>
                                @endforeach
                            @else
                                <h3 class="documents-group-title">Mandatory Uploads (Core Documents)</h3>
                                @foreach ($mandatoryDocumentTypes as $documentType => $label)
                                    <label class="documents-field">
                                        <strong>{{ $label }} <span class="documents-required" aria-label="required">*</span></strong>
                                        <input type="file" name="document_uploads[{{ $documentType }}]" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp" required {{ $documentSubmissionLocked ? 'disabled' : '' }}>
                                    </label>
                                @endforeach

                                <h3 class="documents-group-title">Optional Uploads</h3>
                                @foreach ($optionalDocumentTypes as $documentType => $label)
                                    <label class="documents-field">
                                        <strong>{{ $label }}</strong>
                                        <input type="file" name="document_uploads[{{ $documentType }}]" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/png,image/jpeg,image/webp" {{ $documentSubmissionLocked ? 'disabled' : '' }}>
                                    </label>
                                @endforeach
                            @endif

                            <small class="documents-helper">Supported formats: PDF, DOC, DOCX, PNG, JPG (Max size: 10MB per file).</small>
                        </div>

                        <div class="documents-actions">
                            <button class="documents-submit" type="submit" {{ $documentSubmissionLocked ? 'disabled' : '' }}>
                                <x-lucide-upload /> {{ $reuploadRequestedDocuments->isNotEmpty() ? 'Submit Reuploaded Docs' : 'Submit Documents' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-documents-step-form]');

            if (!form) {
                return;
            }

            form.addEventListener('submit', (event) => {
                const hasFile = Array.from(form.querySelectorAll('input[type="file"]:not(:disabled)')).some((input) => input.files.length > 0);

                if (!hasFile) {
                    event.preventDefault();
                    alert('Please attach at least one document before submitting.');
                    return;
                }

                if (form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = 'true';
                form.querySelectorAll('button[type="submit"]').forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Submitting Documents...';
                });
            });
        });
    </script>
@endsection
