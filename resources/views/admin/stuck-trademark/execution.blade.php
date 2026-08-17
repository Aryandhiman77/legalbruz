@extends('layouts.app')

@section('content')
    @php
        $auditRecommendation = $case->audit_recommendation ?: $case->execution_recommendation ?: $case->audit_summary;
        $subStage = $case->execution_sub_stage ?: ($case->execution_started_at ? 'required_actions' : 'not_started');
        $visibleSubStage = $subStage === 'additional_action_progress' ? 'additional_action_review' : $subStage;
        $subSteps = [
            'execution_started' => ['label' => 'Execution Started', 'copy' => 'Your recovery execution has started.', 'icon' => 'play-circle'],
            'required_actions' => ['label' => 'Required Actions Confirmed', 'copy' => 'Required recovery actions are confirmed for your case.', 'icon' => 'list-checks'],
            'action_progress' => ['label' => 'Action in Progress', 'copy' => 'We are working on the selected recovery actions.', 'icon' => 'settings'],
            'status_monitoring' => ['label' => 'Status Monitoring Active', 'copy' => 'We are monitoring your trademark status for movement.', 'icon' => 'trending-up'],
            'additional_action_review' => ['label' => $case->execution_additional_action_name ?: 'Additional Action Required, if any', 'copy' => $case->execution_additional_action_description ?: 'We will notify you if any additional action is needed.', 'icon' => 'bell'],
            'execution_completed' => ['label' => 'Execution Completed', 'copy' => 'Execution work completed and case moved for resolution.', 'icon' => 'flag'],
        ];
        $subStageOrder = array_keys($subSteps);
        $currentSubStageIndex = array_search($visibleSubStage, $subStageOrder, true);
        $currentSubStageIndex = $currentSubStageIndex === false ? -1 : $currentSubStageIndex;
        $executionIsComplete = (bool) $case->execution_completed_at || $subStage === 'execution_completed';
        if ($executionIsComplete) {
            $visibleSubStage = 'execution_completed';
            $currentSubStageIndex = array_search($visibleSubStage, $subStageOrder, true);
        }
    @endphp

    <style>
        .execution-page { color: #10233f; }
        .execution-shell { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 18px; align-items: start; }
        .execution-card { border: 0; border-radius: 10px; background: #fff; box-shadow: 0 10px 28px rgba(15, 23, 42, .08); overflow: hidden; }
        .execution-card-header { padding: 18px 22px; background: #27466d; color: #fff; }
        .execution-card-header h3 { margin: 0; color: #fff; font-size: 1.08rem; font-weight: 900; }
        .execution-card-body { padding: 22px; }
        .execution-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .execution-field { padding: 13px; border: 1px solid #dbe7f3; border-radius: 8px; background: #f8fafc; }
        .execution-field span { display: block; margin-bottom: 5px; color: #64748b; font-size: .72rem; font-weight: 900; text-transform: uppercase; }
        .execution-field strong, .execution-field p { margin: 0; font-weight: 850; overflow-wrap: anywhere; }
        .execution-substeps { display: grid; gap: 14px; }
        .execution-substep { display: grid; grid-template-columns: 38px minmax(0, 1fr) auto; gap: 12px; align-items: center; }
        .execution-substep-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid #cfe0f6; color: #2563eb; background: #eef6ff; }
        .execution-substep.is-completed .execution-substep-icon { color: #0f9f6e; background: #e8fbf4; border-color: #9ce7c8; }
        .execution-substep.is-current .execution-substep-icon { color: #fff; background: #2563eb; border-color: #2563eb; }
        .execution-substep h4 { margin: 0; font-size: .98rem; font-weight: 950; }
        .execution-substep p { margin: 2px 0 0; color: #53657d; font-size: .86rem; line-height: 1.35; }
        .execution-substep-status { font-weight: 900; color: #b45309; }
        .execution-substep.is-completed .execution-substep-status { color: #0f9f6e; }
        .execution-substep.is-current .execution-substep-status { color: #2563eb; }
        .optional-doc-list { display: grid; gap: 8px; }
        .optional-doc-item { display: flex; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid #dbe7f3; border-radius: 8px; background: #f8fafc; }
        @media (max-width: 1199.98px) { .execution-shell { grid-template-columns: 1fr; } }
        @media (max-width: 767.98px) { .execution-summary { grid-template-columns: 1fr; } .execution-substep { grid-template-columns: 34px minmax(0, 1fr); } .execution-substep-status { grid-column: 2; } }
    </style>

    <div class="container-fluid py-4 execution-page">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Execution Work</h1>
                <p class="text-muted mb-0">{{ $case->case_number }} | {{ $case->trademark_name }}</p>
            </div>
            <a href="{{ route('admin.stuck-trademark.show', $case) }}" class="btn btn-outline-secondary">Back to Case</a>
        </div>

        @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the highlighted fields.</strong>
                <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="execution-shell">
            <div class="execution-card">
                <div class="execution-card-header"><h3>Case & Execution Summary</h3></div>
                <div class="execution-card-body">
                    <div class="execution-summary mb-3">
                        <div class="execution-field"><span>Applicant</span><strong>{{ $case->applicant_name }}</strong></div>
                        <div class="execution-field"><span>Brand</span><strong>{{ $case->trademark_name }}</strong></div>
                        <div class="execution-field"><span>Application</span><strong>{{ $case->application_number ?: 'Not provided' }}</strong></div>
                        <div class="execution-field"><span>Class</span><strong>{{ $case->trademark_class ?: 'Not provided' }}</strong></div>
                        <div class="execution-field"><span>Payment</span><strong>{{ ucwords(str_replace('_', ' ', $case->execution_payment_status ?: 'not requested')) }}</strong></div>
                        <div class="execution-field"><span>Status</span><strong>{{ $case->execution_status ?: 'Not started' }}</strong></div>
                    </div>
                    <div class="execution-field">
                        <span>Audit Recommendation</span>
                        <p>{{ $auditRecommendation ?: 'No audit recommendation added yet.' }}</p>
                    </div>
                </div>
            </div>

            <div class="execution-card">
                <div class="execution-card-header"><h3>Recovery Sub Stage</h3></div>
                <div class="execution-card-body">
                    <div class="execution-substeps">
                        @foreach ($subSteps as $key => $step)
                            @php
                                $index = array_search($key, $subStageOrder, true);
                                $isCompleted = $executionIsComplete
                                    || $currentSubStageIndex > $index
                                    || ($key === 'execution_started' && $case->execution_started_at);
                                $isCurrent = ! $executionIsComplete && $currentSubStageIndex === $index;
                            @endphp
                            <div class="execution-substep {{ $isCompleted ? 'is-completed' : '' }} {{ $isCurrent ? 'is-current' : '' }}">
                                <span class="execution-substep-icon"><x-dynamic-component :component="'lucide-' . $step['icon']" /></span>
                                <div>
                                    <h4>{{ $step['label'] }}</h4>
                                    <p>{{ $step['copy'] }}</p>
                                </div>
                                <span class="execution-substep-status">{{ $isCompleted ? 'Completed' : ($isCurrent ? 'Active' : 'Pending') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="execution-card">
                <div class="execution-card-header"><h3>Current Admin Action</h3></div>
                <div class="execution-card-body">
                    @if ($case->execution_payment_status !== 'paid')
                        <p class="text-muted mb-0">Execution actions unlock after the client purchases the execution package.</p>
                    @elseif (! $case->execution_started_at)
                        <form action="{{ route('admin.trademark-execution.start', $case) }}" method="POST">
                            @csrf
                            <p class="text-muted">Start the revival execution and notify the client that execution has started.</p>
                            <button type="submit" class="btn btn-primary">Start Revival Execution</button>
                        </form>
                    @elseif ($subStage === 'required_actions')
                        <form action="{{ route('admin.trademark-execution.actions', $case) }}" method="POST" enctype="multipart/form-data" data-execution-form>
                            @csrf
                            <h5 class="fw-bold mb-3">Required Actions Confirmed</h5>
                            <div class="table-responsive mb-3">
                                <table class="table align-middle">
                                    <thead><tr><th>Required</th><th>Action</th><th>Status</th><th>Admin Note</th></tr></thead>
                                    <tbody>
                                        @foreach ($actions as $actionName)
                                            @php $action = $case->executionActions->firstWhere('action_name', $actionName); @endphp
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="actions[{{ $loop->index }}][action_name]" value="{{ $actionName }}">
                                                    <input class="form-check-input" type="checkbox" name="actions[{{ $loop->index }}][is_required]" value="1" @checked($action?->is_required)>
                                                </td>
                                                <td class="fw-bold">{{ $actionName }}</td>
                                                <td>
                                                    <select name="actions[{{ $loop->index }}][status]" class="form-select">
                                                        @foreach ($actionStatuses as $status)
                                                            <option value="{{ $status }}" @selected(($action?->status ?: 'Not Required') === $status)>{{ $status }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><textarea name="actions[{{ $loop->index }}][admin_note]" rows="2" class="form-control">{{ $action?->admin_note }}</textarea></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <label class="form-label">Client-facing note</label>
                            <textarea name="note" rows="3" class="form-control mb-3" placeholder="Optional note for client"></textarea>
                            @include('admin.stuck-trademark.partials.execution-optional-documents')
                            <button type="submit" class="btn btn-success mt-3">Submit Required Actions</button>
                        </form>
                    @elseif ($subStage === 'action_progress')
                        <form action="{{ route('admin.trademark-execution.action-progress', $case) }}" method="POST" enctype="multipart/form-data" data-execution-form>
                            @csrf
                            <h5 class="fw-bold mb-3">Action in Progress</h5>
                            <label class="form-label">Proof / Update</label>
                            <input type="text" name="title" class="form-control mb-3" placeholder="Example: Registry follow-up submitted" required>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select mb-3" required>
                                @foreach ($progressStatuses as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach
                            </select>
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="3" class="form-control mb-3" placeholder="Client-facing update"></textarea>
                            @include('admin.stuck-trademark.partials.execution-optional-documents')
                            <button type="submit" class="btn btn-success mt-3">Submit Progress</button>
                        </form>
                    @elseif ($subStage === 'status_monitoring')
                        <form action="{{ route('admin.trademark-execution.status-monitoring', $case) }}" method="POST" enctype="multipart/form-data" data-execution-form>
                            @csrf
                            <h5 class="fw-bold mb-3">Status Monitoring Active</h5>
                            <label class="form-label">Optional Note</label>
                            <textarea name="note" rows="3" class="form-control mb-3" placeholder="Add registry movement/status monitoring note"></textarea>
                            @include('admin.stuck-trademark.partials.execution-optional-documents')
                            <button type="submit" class="btn btn-success mt-3">Complete Status Monitoring</button>
                        </form>
                    @elseif ($subStage === 'additional_action_review')
                        <form action="{{ route('admin.trademark-execution.additional-action', $case) }}" method="POST" enctype="multipart/form-data" data-execution-form data-additional-action-form>
                            @csrf
                            <h5 class="fw-bold mb-3">Additional Action Required, if any?</h5>
                            <label class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="additional_action_required" value="1" data-additional-action-toggle>
                                <span class="form-check-label">Additional action required, if any?</span>
                            </label>
                            <div class="d-none" data-additional-action-fields>
                                <label class="form-label">Action Name</label>
                                <input type="text" name="action_name" class="form-control mb-3" placeholder="Example: Fresh registry follow-up">
                                <label class="form-label">Action Description</label>
                                <textarea name="action_description" rows="3" class="form-control mb-3" placeholder="Describe what action is required"></textarea>
                            </div>
                            <label class="form-label">Optional Note</label>
                            <textarea name="note" rows="3" class="form-control mb-3" placeholder="Used when no additional action is required"></textarea>
                            @include('admin.stuck-trademark.partials.execution-optional-documents')
                            <button type="submit" class="btn btn-success mt-3">Submit</button>
                        </form>
                    @elseif ($subStage === 'additional_action_progress')
                        <form action="{{ route('admin.trademark-execution.additional-action', $case) }}" method="POST" enctype="multipart/form-data" data-execution-form>
                            @csrf
                            <h5 class="fw-bold mb-1">{{ $case->execution_additional_action_name }}</h5>
                            <p class="text-muted">{{ $case->execution_additional_action_description }}</p>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select mb-3" required>
                                @foreach ($progressStatuses as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach
                            </select>
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="3" class="form-control mb-3" placeholder="Client-facing completion note"></textarea>
                            @include('admin.stuck-trademark.partials.execution-optional-documents')
                            <button type="submit" class="btn btn-success mt-3">Complete Additional Action</button>
                        </form>
                    @else
                        <p class="text-success fw-bold mb-0">Execution completed.</p>
                    @endif
                </div>
            </div>

            <div class="execution-card">
                <div class="execution-card-header"><h3>Shared Execution Documents</h3></div>
                <div class="execution-card-body">
                    @forelse ($case->executionDocuments as $document)
                        <div class="optional-doc-item">
                            <span>
                                <strong>{{ $document->document_title }}</strong>
                                <span class="d-block text-muted small">{{ $document->execution_stage ?: $document->document_type }}</span>
                            </span>
                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-outline-success btn-sm">View</a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No execution documents attached yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="executionOptionalDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attach Optional Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Document Name</label>
                    <input type="text" class="form-control mb-3" data-execution-doc-name placeholder="Example: Registry status screenshot">
                    <label class="form-label">Document File</label>
                    <input type="file" class="form-control" data-execution-doc-file>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-execution-doc-save>Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let activeForm = null;
            let pendingFileInput = null;
            const modalElement = document.getElementById('executionOptionalDocumentModal');
            const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
            const nameInput = document.querySelector('[data-execution-doc-name]');

            document.querySelectorAll('[data-execution-open-doc-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    activeForm = button.closest('form');
                    pendingFileInput = null;
                    nameInput.value = '';
                    const currentFileInput = document.querySelector('[data-execution-doc-file]');
                    if (currentFileInput) {
                        currentFileInput.value = '';
                    }
                    modal?.show();
                });
            });

            document.querySelector('[data-execution-doc-save]')?.addEventListener('click', () => {
                const currentFileInput = document.querySelector('[data-execution-doc-file]');
                if (!activeForm || !nameInput.value.trim() || !currentFileInput?.files?.length) {
                    currentFileInput?.reportValidity();
                    return;
                }

                const fields = activeForm.querySelector('[data-execution-optional-doc-fields]');
                const list = activeForm.querySelector('[data-execution-optional-doc-list]');
                const empty = list?.querySelector('[data-execution-optional-doc-empty]');
                const movedFile = currentFileInput;
                const replacementInput = document.createElement('input');
                replacementInput.type = 'file';
                replacementInput.className = 'form-control';
                replacementInput.setAttribute('data-execution-doc-file', '');
                movedFile.parentElement?.appendChild(replacementInput);
                movedFile.name = 'optional_documents[]';
                movedFile.removeAttribute('data-execution-doc-file');
                movedFile.classList.add('d-none');

                const nameField = document.createElement('input');
                nameField.type = 'hidden';
                nameField.name = 'optional_document_names[]';
                nameField.value = nameInput.value.trim();

                fields.appendChild(nameField);
                fields.appendChild(movedFile);
                empty?.remove();

                const row = document.createElement('div');
                row.className = 'optional-doc-item';
                row.innerHTML = `<span><strong></strong><span class="d-block text-muted small"></span></span>`;
                row.querySelector('strong').textContent = nameInput.value.trim();
                row.querySelector('.small').textContent = movedFile.files[0].name;
                list.appendChild(row);

                modal?.hide();
            });

            document.querySelector('[data-additional-action-toggle]')?.addEventListener('change', (event) => {
                document.querySelector('[data-additional-action-fields]')?.classList.toggle('d-none', !event.target.checked);
            });
        });
    </script>
@endsection
