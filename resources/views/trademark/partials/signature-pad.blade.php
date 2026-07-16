@php
    $padId = $id ?? 'signature-pad';
    $defaultName = $defaultName ?? auth()->user()->name ?? '';
    $label = $label ?? 'Signature';
    $namePrefix = $namePrefix ?? '';
    $fieldPrefix = $namePrefix ? $namePrefix . '_' : '';
    $theme = $theme ?? 'blue';
    $signUrl = $signUrl ?? null;
    $signedViewTarget = $signedViewTarget ?? null;
    $signedDownloadTarget = $signedDownloadTarget ?? null;
    $formId = $formId ?? null;
    $applied = $applied ?? false;
    $signatureBoxInfo = $signatureBoxInfo ?? null;
    $signatureBoxSize = is_array($signatureBoxInfo) ? ($signatureBoxInfo['label'] ?? null) : null;
    $signatureBoxWidth = is_array($signatureBoxInfo) ? ($signatureBoxInfo['width'] ?? null) : null;
    $signatureBoxHeight = is_array($signatureBoxInfo) ? ($signatureBoxInfo['height'] ?? null) : null;
@endphp

<div
    class="signature-pad signature-pad-{{ $theme }} {{ $applied ? 'signature-is-applied' : '' }}"
    data-signature-pad
    data-signature-applied="{{ $applied ? 'true' : 'false' }}"
    @if ($signUrl) data-sign-url="{{ $signUrl }}" @endif
    @if ($signedViewTarget) data-signed-view-target="{{ $signedViewTarget }}" @endif
    @if ($signedDownloadTarget) data-signed-download-target="{{ $signedDownloadTarget }}" @endif
    @if ($formId) data-signature-form-id="{{ $formId }}" @endif
    @if ($signatureBoxWidth && $signatureBoxHeight) data-signature-image-width="{{ $signatureBoxWidth }}" data-signature-image-height="{{ $signatureBoxHeight }}" @endif
    id="{{ $padId }}"
>
    @if ($label !== '')
        <label class="form-label fw-semibold">{{ $label }}</label>
    @endif
    <input type="hidden" name="{{ $fieldPrefix }}signature_mode" value="draw" data-signature-mode @if ($formId) form="{{ $formId }}" @endif>
    <input type="hidden" name="{{ $fieldPrefix }}digital_signature" value="{{ old($fieldPrefix . 'digital_signature', $defaultName) }}" data-signature-value @if ($formId) form="{{ $formId }}" @endif>
    <input type="hidden" name="{{ $fieldPrefix }}signature_image_data" data-signature-image-data @if ($formId) form="{{ $formId }}" @endif>

    <div class="signature-tabs" role="tablist">
        <button type="button" class="active" data-signature-tab="draw">Draw</button>
        <button type="button" data-signature-tab="type">Type</button>
        <button type="button" data-signature-tab="upload">Upload</button>
    </div>

    <div class="signature-panel active" data-signature-panel="draw">
        <p>Use your mouse or touchpad to draw your signature</p>
        <canvas class="signature-canvas" width="1800" height="520" data-signature-canvas></canvas>
        <div class="signature-tools">
            <button type="button" data-signature-clear><i class="bi bi-trash"></i> Clear</button>
            <button type="button" data-signature-undo><i class="bi bi-arrow-counterclockwise"></i> Undo</button>
        </div>
    </div>

    <div class="signature-panel" data-signature-panel="type">
        <p>Type your full legal name as your electronic signature</p>
        <input type="text" class="form-control signature-type-input" value="{{ old($fieldPrefix . 'digital_signature', $defaultName) }}" data-signature-type-input placeholder="Type your full legal name">
        <div class="signature-type-preview" data-signature-type-preview>{{ old($fieldPrefix . 'digital_signature', $defaultName) }}</div>
    </div>

    <div class="signature-panel" data-signature-panel="upload">
        <p>Upload a clear PNG or JPG image of your signature</p>
        @if ($signatureBoxSize)
            <small class="signature-box-size">Image size : {{ $signatureBoxSize }}</small>
        @endif
        <input type="file" class="form-control" name="{{ $fieldPrefix }}signature_image" accept=".png,.jpg,.jpeg" data-signature-upload @if ($formId) form="{{ $formId }}" @endif>
        <div class="signature-cropper" data-signature-cropper hidden>
            <div class="signature-crop-stage" data-signature-crop-stage>
                <img src="" alt="Align uploaded signature" data-signature-crop-image draggable="false">
            </div>
            <div class="signature-crop-controls">
                <label>
                    Zoom
                    <input type="range" min="0.4" max="3" step="0.01" value="1" data-signature-crop-zoom>
                </label>
                <button type="button" data-signature-crop-use>Use Cropped Signature</button>
            </div>
        </div>
        <div class="signature-upload-preview" data-signature-upload-preview>
            <span>No signature image selected</span>
            <img src="" alt="Uploaded signature preview" data-signature-upload-image hidden>
        </div>
    </div>

    <label class="signature-agreement">
        <input type="checkbox" data-signature-agree>
        <span>I agree that this electronic signature has the same intent and legal effect as my handwritten signature.</span>
    </label>

    <button type="button" class="signature-apply-btn" data-signature-apply>Apply Signature</button>
    <div class="signature-applied" data-signature-applied @if (!$applied) hidden @endif>
        <span>Signature applied.</span>
        <button type="button" data-signature-reapply>Reapply Signature</button>
    </div>
</div>
