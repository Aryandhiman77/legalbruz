<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Config as PdfParserConfig;
use Smalot\PdfParser\Parser as PdfParser;

class PdfSignatureStampService
{
    private const PLACEHOLDER_PATTERN = '/(?<!_)_{13}(?!_)/';
    private const MM_PER_POINT = 25.4 / 72;
    private const PLACEMENT_DESIGN_WIDTH_MM = 210.0;
    private const PLACEMENT_DESIGN_HEIGHT_MM = 297.0;

    public function generateSignedWorkflowPdf(
        Application $application,
        Document $sourceDocument,
        string $signatureText,
        ?string $notes = null,
        string $status = 'uploaded',
        ?string $signatureImagePath = null
    ): Document {
        $signatureText = trim($signatureText);

        if ($signatureText === '' && !$signatureImagePath) {
            throw new \RuntimeException('A signature is required to prepare the signed PDF.');
        }

        if (!$this->isPdfDocument($sourceDocument)) {
            throw new \RuntimeException(ucwords(str_replace('_', ' ', $sourceDocument->document_type)) . ' must be uploaded as a PDF for digital signing.');
        }

        $sourcePath = storage_path('app/public/' . $sourceDocument->file_path);

        if (!is_file($sourcePath)) {
            throw new \RuntimeException('The original ' . str_replace('_', ' ', $sourceDocument->document_type) . ' PDF could not be found.');
        }

        return $this->createSignedDocument(
            $application,
            $sourceDocument->document_type,
            $sourcePath,
            $signatureText,
            $notes,
            $status,
            $signatureImagePath
        );
    }

    public function generateSignedUploadedPdf(
        Application $application,
        string $documentType,
        UploadedFile $uploadedFile,
        string $signatureText,
        ?string $notes = null,
        string $status = 'uploaded',
        ?string $signatureImagePath = null
    ): Document {
        $signatureText = trim($signatureText);

        if ($signatureText === '' && !$signatureImagePath) {
            throw new \RuntimeException('A signature is required to prepare the signed PDF.');
        }

        $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());

        if ($extension !== 'pdf') {
            throw new \RuntimeException(ucwords(str_replace('_', ' ', $documentType)) . ' must be uploaded as a PDF for digital signing.');
        }

        $directory = storage_path('app/temp-onboarding');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $sourcePath = $directory . '/' . $documentType . '-' . $application->id . '-' . now()->timestamp . '.pdf';
        $uploadedFile->move($directory, basename($sourcePath));

        try {
            return $this->createSignedDocument(
                $application,
                $documentType,
                $sourcePath,
                $signatureText,
                $notes,
                $status,
                $signatureImagePath
            );
        } finally {
            if (is_file($sourcePath)) {
                @unlink($sourcePath);
            }
        }
    }

    private function extractSignaturePlacements(string $sourcePath): array
    {
        $config = new PdfParserConfig();
        $config->setDataTmFontInfoHasToBeIncluded(true);

        $parser = new PdfParser([], $config);
        $document = $parser->parseFile($sourcePath);
        $placementsByPage = [];

        foreach ($document->getPages() as $pageIndex => $page) {
            foreach ($page->getDataTm() as $item) {
                $tm = $item[0] ?? null;
                $text = (string) ($item[1] ?? '');

                if (!is_array($tm) || $text === '') {
                    continue;
                }

                if (!preg_match_all(self::PLACEHOLDER_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE)) {
                    continue;
                }

                $fontSize = max((float) ($item[3] ?? 12), 9.0);

                foreach ($matches[0] as $match) {
                    $placeholder = (string) $match[0];
                    $offset = (int) $match[1];
                    $prefix = substr($text, 0, $offset);

                    $placementsByPage[$pageIndex + 1][] = [
                        'x_pt' => (float) ($tm[4] ?? 0),
                        'y_pt' => (float) ($tm[5] ?? 0),
                        'font_size_pt' => $fontSize,
                        'prefix' => $prefix,
                        'placeholder' => $placeholder,
                    ];
                }
            }
        }

        return $placementsByPage;
    }

    private function buildSignedPdf(
        Application $application,
        string $documentType,
        string $sourcePath,
        string $signatureText,
        array $placementsByPage,
        ?string $signatureImagePath = null
    ): string {
        $pdf = new Fpdi();
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $normalizedSourcePath = null;

        try {
            $pageCount = $pdf->setSourceFile($sourcePath);
        } catch (\Throwable $exception) {
            if (!str_contains($exception->getMessage(), 'compression technique')) {
                throw $exception;
            }

            $normalizedSourcePath = $this->normalizePdfForFpdi($sourcePath);

            if (!$normalizedSourcePath) {
                throw new \RuntimeException('This PDF cannot be digitally signed without reducing quality because of its internal compression format. Please install qpdf on the server or ask the admin to re-upload a standard exported PDF.');
            }

            $pdf = new Fpdi();
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            $pageCount = $pdf->setSourceFile($normalizedSourcePath);
        }

        try {
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pageSize = $pdf->getTemplateSize($templateId);
                $orientation = ($pageSize['width'] ?? 0) > ($pageSize['height'] ?? 0) ? 'L' : 'P';

                $pdf->AddPage($orientation, [$pageSize['width'], $pageSize['height']]);
                $pdf->useTemplate($templateId, 0, 0, $pageSize['width'], $pageSize['height'], true);

                foreach ($placementsByPage[$pageNo] ?? [] as $placement) {
                    if (($placement['source'] ?? null) === 'manual') {
                        $this->applyManualOverlay(
                            $pdf,
                            $placement,
                            $signatureText,
                            $signatureImagePath,
                            $documentType,
                            (float) $pageSize['width'],
                            (float) $pageSize['height']
                        );
                    } else {
                        $this->applySignatureOverlay($pdf, (float) $pageSize['height'], $placement, $signatureText, $signatureImagePath, $documentType);
                    }
                }
            }

            $filename = 'signed-' . $documentType . '-' . $application->id . '-' . now()->timestamp . '.pdf';
            $relativePath = 'documents/signed/' . $filename;
            $absolutePath = storage_path('app/public/' . $relativePath);

            $directory = dirname($absolutePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->Output('F', $absolutePath);

            return $relativePath;
        } finally {
            if ($normalizedSourcePath && is_file($normalizedSourcePath)) {
                @unlink($normalizedSourcePath);
            }
        }
    }

    private function normalizePdfForFpdi(string $sourcePath): ?string
    {
        $directory = storage_path('app/temp-onboarding');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return $this->normalizePdfWithQpdf($sourcePath, $directory)
            ?? null;
    }

    private function normalizePdfWithQpdf(string $sourcePath, string $directory): ?string
    {
        $qpdfPath = $this->findExecutable('qpdf');

        if (!$qpdfPath) {
            return null;
        }

        $normalizedPath = $directory . '/normalized-qpdf-' . uniqid('', true) . '.pdf';
        $command = escapeshellarg($qpdfPath)
            . ' --object-streams=disable --stream-data=uncompress '
            . escapeshellarg($sourcePath)
            . ' '
            . escapeshellarg($normalizedPath)
            . ' 2>&1';

        return $this->runPdfNormalizationCommand($command, $normalizedPath);
    }

    private function runPdfNormalizationCommand(string $command, string $normalizedPath): ?string
    {
        exec($command, $output, $exitCode);

        if ($exitCode === 0 && is_file($normalizedPath) && filesize($normalizedPath) > 0) {
            return $normalizedPath;
        }

        if (is_file($normalizedPath)) {
            @unlink($normalizedPath);
        }

        return null;
    }

    private function findExecutable(string $binary): ?string
    {
        $command = 'command -v ' . escapeshellarg($binary) . ' 2>/dev/null';
        $path = trim((string) shell_exec($command));

        return $path !== '' && is_executable($path) ? $path : null;
    }

    private function createSignedDocument(
        Application $application,
        string $documentType,
        string $sourcePath,
        string $signatureText,
        ?string $notes = null,
        string $status = 'uploaded',
        ?string $signatureImagePath = null
    ): Document {
        $signedDocumentType = $documentType . ' (Signed)';
        $this->deleteExistingSignedDocument($application, $signedDocumentType);

        $placementsByPage = $this->manualPlacementsByPage($application, $documentType);

        if ($placementsByPage === []) {
            $placementsByPage = $this->extractSignaturePlacements($sourcePath);
        }

        if ($placementsByPage === []) {
            throw new \RuntimeException('No signing fields are configured for the ' . str_replace('_', ' ', $documentType) . ' PDF. Please ask the admin to set the signature/date positions and resend the onboarding package.');
        }

        $relativePath = $this->buildSignedPdf($application, $documentType, $sourcePath, $signatureText, $placementsByPage, $signatureImagePath);

        return Document::create([
            'application_id' => $application->id,
            'user_id' => Auth::id() ?: $application->user_id,
            'document_type' => $signedDocumentType,
            'file_path' => $relativePath,
            'file_name' => basename($relativePath),
            'file_type' => 'pdf',
            'file_size' => Storage::disk('public')->size($relativePath),
            'status' => $status,
            'verification_notes' => 'Electronically signed by user: ' . ($signatureText ?: 'Signature image') . ($notes ? ' Notes: ' . $notes : ''),
        ]);
    }

    private function manualPlacementsByPage(Application $application, string $documentType): array
    {
        $fields = data_get($application->workflow_meta ?? [], 'signature_fields.' . $documentType, []);

        if (!is_array($fields) || $fields === []) {
            return [];
        }

        $placementsByPage = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $page = max((int) ($field['page'] ?? 1), 1);
            $type = in_array(($field['type'] ?? ''), ['signature', 'date'], true)
                ? $field['type']
                : 'signature';

            $placementsByPage[$page][] = [
                'source' => 'manual',
                'type' => $type,
                'x_mm' => max((float) ($field['x'] ?? 0), 0),
                'y_mm' => max((float) ($field['y'] ?? 0), 0),
                'width_mm' => max((float) ($field['width'] ?? 0), 10),
                'height_mm' => max((float) ($field['height'] ?? 0), 5),
                'label' => $field['label'] ?? ($type === 'date' ? 'Date' : 'Applicant Signature'),
            ];
        }

        return $placementsByPage;
    }

    private function applyManualOverlay(
        Fpdi $pdf,
        array $placement,
        string $signatureText,
        ?string $signatureImagePath = null,
        ?string $documentType = null,
        ?float $pageWidthMm = null,
        ?float $pageHeightMm = null
    ): void {
        $placement = $this->scaleManualPlacementForPage($placement, $pageWidthMm, $pageHeightMm);
        $x = (float) $placement['x_mm'];
        $y = (float) $placement['y_mm'];
        $width = (float) $placement['width_mm'];
        $height = (float) $placement['height_mm'];
        $type = (string) ($placement['type'] ?? 'signature');

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(
            max($x - 1, 0),
            max($y - 1, 0),
            $width + 2,
            $height + 2,
            'F'
        );

        if ($type === 'date') {
            $this->applyStartText($pdf, now()->format('d/m/Y'), $x, $y, $width, $height, 11);
            return;
        }

        if ($signatureImagePath && is_file($signatureImagePath)) {
            $imageSize = @getimagesize($signatureImagePath);
            $imageWidthPx = max((int) ($imageSize[0] ?? 1), 1);
            $imageHeightPx = max((int) ($imageSize[1] ?? 1), 1);
            $imageWidthMm = $width;
            $imageHeightMm = $imageWidthMm * ($imageHeightPx / $imageWidthPx);

            if ($imageHeightMm > $height) {
                $imageHeightMm = $height;
                $imageWidthMm = $imageHeightMm * ($imageWidthPx / $imageHeightPx);
            }

            $imageX = $x + max(($width - $imageWidthMm) / 2, 0);
            $imageY = $y + max(($height - $imageHeightMm) / 2, 0);

            $pdf->Image($signatureImagePath, $imageX, $imageY, $imageWidthMm, $imageHeightMm);
            return;
        }

        $this->applyStartText($pdf, $signatureText, $x, $y, $width, $height, in_array($documentType, ['engagement_letter', 'poa'], true) ? 18 : 14);
    }

    private function scaleManualPlacementForPage(array $placement, ?float $pageWidthMm, ?float $pageHeightMm): array
    {
        if (!$pageWidthMm || !$pageHeightMm) {
            return $placement;
        }

        $scaleX = $pageWidthMm / self::PLACEMENT_DESIGN_WIDTH_MM;
        $scaleY = $pageHeightMm / self::PLACEMENT_DESIGN_HEIGHT_MM;

        foreach (['x_mm', 'width_mm'] as $key) {
            $placement[$key] = max((float) ($placement[$key] ?? 0), 0) * $scaleX;
        }

        foreach (['y_mm', 'height_mm'] as $key) {
            $placement[$key] = max((float) ($placement[$key] ?? 0), 0) * $scaleY;
        }

        $placement['x_mm'] = min((float) $placement['x_mm'], max($pageWidthMm - 1, 0));
        $placement['y_mm'] = min((float) $placement['y_mm'], max($pageHeightMm - 1, 0));
        $placement['width_mm'] = min((float) $placement['width_mm'], max($pageWidthMm - (float) $placement['x_mm'], 1));
        $placement['height_mm'] = min((float) $placement['height_mm'], max($pageHeightMm - (float) $placement['y_mm'], 1));

        return $placement;
    }

    private function applyCenteredText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize): void
    {
        $fontSize = max($fontSize, 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Times', '', $fontSize);
        $textWidth = $pdf->GetStringWidth($text);

        while ($textWidth > ($width - 2) && $fontSize > 7) {
            $fontSize -= 0.5;
            $pdf->SetFont('Times', '', $fontSize);
            $textWidth = $pdf->GetStringWidth($text);
        }

        $textX = $x + max(($width - $textWidth) / 2, 0);
        $textY = $y + ($height / 2) + ($this->pointsToMm($fontSize) / 2.8);
        $pdf->Text($textX, $textY, $text);
    }

    private function applyStartText(Fpdi $pdf, string $text, float $x, float $y, float $width, float $height, float $fontSize): void
    {
        $fontSize = max($fontSize, 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Times', '', $fontSize);
        $textWidth = $pdf->GetStringWidth($text);

        while ($textWidth > ($width - 2) && $fontSize > 7) {
            $fontSize -= 0.5;
            $pdf->SetFont('Times', '', $fontSize);
            $textWidth = $pdf->GetStringWidth($text);
        }

        $textX = $x + 1;
        $textY = $y + ($height / 2) + ($this->pointsToMm($fontSize) / 2.8);
        $pdf->Text($textX, $textY, $text);
    }

    private function applySignatureOverlay(
        Fpdi $pdf,
        float $pageHeightMm,
        array $placement,
        string $signatureText,
        ?string $signatureImagePath = null,
        ?string $documentType = null
    ): void
    {
        $fontSize = (float) $placement['font_size_pt'];
        $pdf->SetFont('Times', '', $fontSize);

        $placeholderX = $this->pointsToMm((float) $placement['x_pt']) + $pdf->GetStringWidth((string) $placement['prefix']);
        $placeholderWidth = max($pdf->GetStringWidth((string) $placement['placeholder']), 30);
        $baselineY = $pageHeightMm - $this->pointsToMm((float) $placement['y_pt']);
        $useLargerSignature = in_array($documentType, ['engagement_letter', 'poa'], true);
        $signatureFitWidth = $useLargerSignature ? min(max($placeholderWidth * 2.25, 52), 86) : $placeholderWidth;
        $signatureX = max($placeholderX - (($signatureFitWidth - $placeholderWidth) / 2), 0);

        $fittedFontSize = $useLargerSignature ? min($fontSize * 1.9, 22) : $fontSize;
        $pdf->SetFont('Times', '', $fittedFontSize);
        $nameWidth = $pdf->GetStringWidth($signatureText);

        while ($nameWidth > ($signatureFitWidth - 1) && $fittedFontSize > 8) {
            $fittedFontSize -= 0.5;
            $pdf->SetFont('Times', '', $fittedFontSize);
            $nameWidth = $pdf->GetStringWidth($signatureText);
        }

        $textX = $signatureX + 1;
        $fontHeightMm = $this->pointsToMm($fittedFontSize);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(
            max($signatureX - 0.8, 0),
            max($baselineY - ($fontHeightMm * 1.05), 0),
            $signatureFitWidth + 1.6,
            $fontHeightMm * 1.45,
            'F'
        );

        if ($signatureImagePath && is_file($signatureImagePath)) {
            $imageSize = @getimagesize($signatureImagePath);
            $imageWidthPx = max((int) ($imageSize[0] ?? 1), 1);
            $imageHeightPx = max((int) ($imageSize[1] ?? 1), 1);
            $imageWidthMm = $useLargerSignature ? min(max($signatureFitWidth, 58), 90) : min(max($placeholderWidth, 34), 58);
            $imageHeightMm = $useLargerSignature
                ? min(max($imageWidthMm * ($imageHeightPx / $imageWidthPx), 12), 26)
                : min(max($imageWidthMm * ($imageHeightPx / $imageWidthPx), 8), 16);
            $imageX = $signatureX + 1;
            $imageY = max($baselineY - $imageHeightMm - ($useLargerSignature ? 2.2 : 1.2), 0);

            $pdf->Image($signatureImagePath, $imageX, $imageY, $imageWidthMm, $imageHeightMm);
            return;
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($textX, $baselineY, $signatureText);
    }

    private function deleteExistingSignedDocument(Application $application, string $signedDocumentType): void
    {
        $application->documents()
            ->where('document_type', $signedDocumentType)
            ->get()
            ->each(function (Document $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $document->delete();
            });
    }

    private function isPdfDocument(Document $document): bool
    {
        $fileType = strtolower((string) $document->file_type);
        $extension = strtolower(pathinfo((string) $document->file_name, PATHINFO_EXTENSION));

        return $fileType === 'pdf' || $extension === 'pdf';
    }

    private function pointsToMm(float $points): float
    {
        return $points * self::MM_PER_POINT;
    }
}
