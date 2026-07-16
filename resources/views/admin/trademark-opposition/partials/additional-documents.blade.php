@php
    $documents = $documents ?? collect();
    $addButtonAttribute = $addButtonAttribute ?? 'data-additional-document-add';
    $listAttribute = $listAttribute ?? 'data-additional-document-list';
    $templateAttribute = $templateAttribute ?? 'data-additional-document-template';
    $rowAttribute = $rowAttribute ?? 'data-additional-document-row';
    $removeAttribute = $removeAttribute ?? 'data-additional-document-remove';
    $emptyAttribute = $emptyAttribute ?? 'data-additional-document-empty';
    $emptyText = $emptyText ?? 'No additional documents attached. You can add up to 10 files of 15 MB each.';
    $label = $label ?? 'Additional Documents';
    $labelSuffix = $labelSuffix ?? '';
    $description = $description ?? '';
    $addButtonText = $addButtonText ?? 'Attach Additional Document';
    $addButtonClass = $addButtonClass ?? '';
    $iconClass = $iconClass ?? '';
    $showVisibility = $showVisibility ?? false;
    $showDocumentType = $showDocumentType ?? true;
    $showAttachmentNote = $showAttachmentNote ?? true;
    $showRemarks = $showRemarks ?? true;
    $showSaveNote = $showSaveNote ?? true;
    $showHeaderButton = $showHeaderButton ?? true;
    $showInitialRowWhenEmpty = $showInitialRowWhenEmpty ?? false;
    $showEmptyState = $showEmptyState ?? true;
    $viewUrlResolver = $viewUrlResolver ?? null;
    $destroyUrlResolver = $destroyUrlResolver ?? null;
    $showExistingRemove = $showExistingRemove ?? true;
    $editButtonText = $editButtonText ?? 'Edit Objection Details';
    $hasSubmittedDocuments = $documents->contains(fn ($document) => ! ($document->is_draft ?? false));
@endphp

@once
    <style>
        .admin-document-empty{margin:.25rem 0 0;color:#607089;font-size:.86rem;font-weight:650}
        .admin-additional-documents-header{margin-top:22px;margin-bottom:16px}
        .admin-document-row{padding:16px 18px;border:1px solid #dfe8f4;border-radius:9px;background:#fbfdff;margin-bottom:12px}
        .admin-document-row.is-existing{display:block;min-height:76px}
        .admin-document-row-head{display:flex;align-items:center;justify-content:space-between;gap:18px;width:100%;margin:0}
        .admin-document-row.is-new .admin-document-row-head{margin-bottom:10px}
        .admin-document-row-head strong{color:#203e68;font-weight:900;overflow-wrap:anywhere;line-height:1.25}
        .admin-document-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .admin-document-remove{border:1px solid #fecaca;background:#fff;color:#b91c1c;border-radius:7px;min-height:32px;padding:0 10px;font-weight:900}
        .admin-document-remove:hover{background:#fee2e2;color:#991b1b}
        @media(max-width:640px){.admin-document-row-head{align-items:flex-start;flex-direction:column}.admin-document-actions{width:100%}}
    </style>
@endonce

<div data-additional-documents-section data-has-submitted-additional-documents="{{ $hasSubmittedDocuments ? '1' : '0' }}">
<button class="admin-btn light mb-3" type="button" data-additional-documents-edit @unless($hasSubmittedDocuments) style="display:none" @endunless>{{ $editButtonText }}</button>
<div data-additional-documents-fields @if($hasSubmittedDocuments) style="display:none" @endif>
<div class="admin-additional-documents-header d-flex align-items-center justify-content-between gap-2">
    @if($iconClass)
        <span class="admin-additional-documents-icon"><i class="{{ $iconClass }}"></i></span>
    @endif
    <div>
        <label class="admin-label mb-0">{{ $label }}@if($labelSuffix) <span>{{ $labelSuffix }}</span>@endif</label>
        @if($description)
            <p class="admin-document-empty">{{ $description }}</p>
        @endif
    </div>
    @if($showHeaderButton)
        <button class="admin-btn admin-btn-secondary {{ $addButtonClass }}" type="button" {{ $addButtonAttribute }}>{{ $addButtonText }}</button>
    @endif
</div>

<div class="{{ $documents->isEmpty() ? 'admin-additional-documents-list-empty' : '' }}" {{ $listAttribute }} data-empty-text="{{ $emptyText }}" @unless($showEmptyState) data-disable-empty-state @endunless>
    @forelse($documents as $additionalDocument)
        @php
            $documentTitle = $additionalDocument->document_title
                ?? $additionalDocument->review_note
                ?? $additionalDocument->file_name
                ?? $additionalDocument->original_name
                ?? 'Additional Document';
            $documentTypeLabel = $additionalDocument->metadata['document_type_label'] ?? \Illuminate\Support\Str::headline((string) ($additionalDocument->document_type ?? 'additional_document'));
            $documentUrl = $viewUrlResolver ? $viewUrlResolver($additionalDocument) : route('admin.trademark-opposition.document.view', [$case, 'evidence', $additionalDocument->id]);
            $destroyUrl = $destroyUrlResolver ? $destroyUrlResolver($additionalDocument) : route('admin.trademark-opposition.additional-document.destroy', [$case, $additionalDocument]);
        @endphp
        <div class="admin-document-row is-existing" {{ $rowAttribute }}>
            <input type="hidden" name="optional_existing_document_ids[]" value="{{ $additionalDocument->id }}">
            <div class="admin-document-row-head">
                <strong>{{ $documentTitle }}</strong>
                <div class="admin-document-actions">
                    <a class="admin-link" href="{{ $documentUrl }}" target="_blank">View</a>
                    @if($showExistingRemove)
                        <button
                            class="admin-document-remove"
                            type="button"
                            data-additional-document-delete
                            data-delete-action="{{ $destroyUrl }}"
                            data-delete-token="{{ csrf_token() }}"
                        >Remove</button>
                    @endif
                </div>
            </div>
            <input class="admin-input mb-2" type="text" name="optional_document_names[]" maxlength="255" placeholder="Document name" value="{{ $documentTitle }}">
            @if($showDocumentType)
                <input class="admin-input mb-2" type="text" name="optional_document_types[]" maxlength="120" placeholder="Document type" value="{{ $documentTypeLabel }}">
            @endif
            @if($showVisibility)
                <select class="admin-input mb-2" name="optional_document_visibilities[]">
                    <option value="client" @selected(($additionalDocument->visibility ?? 'client') === 'client')>Visible to Client</option>
                    <option value="admin" @selected(($additionalDocument->visibility ?? 'client') === 'admin')>Admin Only</option>
                </select>
            @endif
            @if($showAttachmentNote)
                <textarea class="admin-textarea mb-2" name="optional_attachment_notes[]" placeholder="Attachment note">{{ $additionalDocument->document_note }}</textarea>
            @endif
            @if($showRemarks)
                <textarea class="admin-textarea mb-2" name="optional_document_remarks[]" placeholder="Remarks">{{ $additionalDocument->remarks }}</textarea>
            @endif
            @if($additionalDocument->is_draft ?? false)
                <p class="admin-document-empty">Draft file: {{ $additionalDocument->original_name }}</p>
            @else
                <p class="admin-document-empty">Saved file: {{ $additionalDocument->original_name ?? $documentTitle }}</p>
            @endif
            @if($showSaveNote)
                <button class="admin-btn light" type="button" data-additional-document-save-note>Save Note</button>
            @endif
        </div>
    @empty
        @if($showInitialRowWhenEmpty)
            <div class="admin-document-row is-new" {{ $rowAttribute }}>
                <div class="admin-document-row-head">
                    <strong>Additional Document</strong>
                    @if($showHeaderButton)
                        <button class="admin-document-remove" type="button" {{ $removeAttribute }}>Remove</button>
                    @endif
                </div>
                <input class="admin-input mb-2" type="text" name="optional_document_names[]" maxlength="255" placeholder="Document name (optional)">
                @if($showDocumentType)
                    <input class="admin-input mb-2" type="text" name="optional_document_types[]" maxlength="120" placeholder="Document type (optional)">
                @endif
                @if($showVisibility)
                    <select class="admin-input mb-2" name="optional_document_visibilities[]">
                        <option value="client" selected>Visible to Client</option>
                        <option value="admin">Admin Only</option>
                    </select>
                @endif
                @if($showAttachmentNote)
                    <textarea class="admin-textarea mb-2" name="optional_attachment_notes[]" placeholder="Attachment note"></textarea>
                @endif
                @if($showRemarks)
                    <textarea class="admin-textarea mb-2" name="optional_document_remarks[]" placeholder="Remarks"></textarea>
                @endif
                <input class="admin-input" type="file" name="optional_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                @if($showSaveNote)
                    <button class="admin-btn light mt-2" type="button" data-additional-document-save-note>Save Note</button>
                @endif
            </div>
        @elseif($showEmptyState)
            <p class="admin-document-empty" {{ $emptyAttribute }}>{{ $emptyText }}</p>
        @endif
    @endforelse
</div>

<template {{ $templateAttribute }}>
    <div class="admin-document-row is-new" {{ $rowAttribute }}>
        <div class="admin-document-row-head">
            <strong>Additional Document</strong>
            <button class="admin-document-remove" type="button" {{ $removeAttribute }}>Remove</button>
        </div>
        <input class="admin-input mb-2" type="text" name="optional_document_names[]" maxlength="255" placeholder="Document name (optional)">
        @if($showDocumentType)
            <input class="admin-input mb-2" type="text" name="optional_document_types[]" maxlength="120" placeholder="Document type (optional)">
        @endif
        @if($showVisibility)
            <select class="admin-input mb-2" name="optional_document_visibilities[]">
                <option value="client" selected>Visible to Client</option>
                <option value="admin">Admin Only</option>
            </select>
        @endif
        @if($showAttachmentNote)
            <textarea class="admin-textarea mb-2" name="optional_attachment_notes[]" placeholder="Attachment note"></textarea>
        @endif
        @if($showRemarks)
            <textarea class="admin-textarea mb-2" name="optional_document_remarks[]" placeholder="Remarks"></textarea>
        @endif
        <input class="admin-input" type="file" name="optional_documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
        @if($showSaveNote)
            <button class="admin-btn light mt-2" type="button" data-additional-document-save-note>Save Note</button>
        @endif
    </div>
</template>
<button class="admin-btn light mt-2" type="button" data-additional-documents-cancel @unless($hasSubmittedDocuments) style="display:none" @endunless>Cancel</button>
</div>
</div>
