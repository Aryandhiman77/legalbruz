<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * DocumentController - Handles document viewing, editing, and saving
 */
class DocumentController extends Controller
{
    /**
     * Save edited document content
     * 
     * POST /documents/save-edited
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveEdited(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'document_id' => 'required|integer|exists:documents,id',
            'content' => 'required|string',
        ], [
            'document_id.required' => 'Document ID is required',
            'document_id.exists' => 'Document not found',
            'content.required' => 'Content is required',
        ]);

        try {
            // Get document
            $document = Document::findOrFail($validated['document_id']);

            // Authorization check - user can only edit their own documents
            $application = $document->application;
            if ($application->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only edit your own documents.',
                ], 403);
            }

            // Sanitize HTML content - strip script tags, iframes, and other unsafe elements
            $cleanContent = self::sanitizeHTML($validated['content']);

            // Prepare new HTML with properly wrapped content
            $htmlContent = self::wrapContentInHTML($cleanContent, $document->id);

            // Get file path
            $filePath = $document->file_path;

            // Update file in storage
            try {
                Storage::disk('public')->put($filePath, $htmlContent);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save file: ' . $e->getMessage(),
                ], 500);
            }

            // Update document record with new file size and status
            $document->update([
                'file_size' => strlen($htmlContent),
                'status' => 'edited',
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document saved successfully',
                'document_id' => $document->id,
                'file_size' => strlen($htmlContent),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sanitize HTML content - remove script tags and unsafe elements
     * 
     * @param string $html
     * @return string
     */
    private static function sanitizeHTML(string $html): string
    {
        // Create a DOMDocument
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<meta charset="UTF-8">' . $html, LIBXML_HTML_NOXMLART);

        // Remove script tags, iframes, and event handlers
        $xpath = new \DOMXPath($dom);
        
        // Remove script tags
        foreach ($xpath->query('//script') as $node) {
            $node->parentNode->removeChild($node);
        }
        
        // Remove iframe tags
        foreach ($xpath->query('//iframe') as $node) {
            $node->parentNode->removeChild($node);
        }

        // Save and return HTML
        $html = $dom->saveHTML();
        
        // Remove the added meta tag
        $html = preg_replace('/<meta charset="UTF-8">/', '', $html, 1);

        return $html;
    }

    /**
     * Wrap sanitized content back in complete HTML structure
     * 
     * @param string $pageContent
     * @param int $documentId
     * @return string
     */
    private static function wrapContentInHTML(string $pageContent, int $documentId): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Document</title>
    <style>
        @page {
            margin: 28px 38px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 16px;
            background: #efefef;
            font-family: "Times New Roman", Times, serif;
            color: #000;
        }

        .toolbar {
            max-width: 820px;
            margin: 0 auto 14px auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
        }

        .toolbar button {
            border: 1px solid #007bff;
            background: #fff;
            color: #007bff;
            padding: 8px 14px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 14px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .toolbar button:hover {
            background: #007bff;
            color: white;
        }

        .toolbar button.save-btn {
            border-color: #28a745;
            color: #28a745;
        }

        .toolbar button.save-btn:hover {
            background: #28a745;
            color: white;
        }

        .toolbar button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .button-spinner {
            display: inline-block;
            width: 0.9em;
            height: 0.9em;
            margin-right: 6px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            vertical-align: -0.12em;
            animation: button-spin 0.7s linear infinite;
        }

        @keyframes button-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .toolbar .status {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            align-self: center;
        }

        .toolbar .status.editing {
            background: #fff3cd;
            color: #856404;
        }

        .toolbar .status.saved {
            background: #d4edda;
            color: #155724;
        }

        .page {
            width: 794px;
            min-height: 1123px;
            margin: 0 auto;
            background: #fff;
            padding: 28px 38px 36px 38px;
            box-shadow: 0 0 10px rgba(0,0,0,0.12);
            line-height: 1.28;
        }

        .page[data-editing="true"] {
            outline: 2px dashed #007bff;
        }

        .editable {
            min-width: 8px;
            display: inline-block;
            outline: none;
            border-radius: 2px;
            padding: 0 2px;
            cursor: text;
        }

        .editable:hover,
        .editable:focus {
            background: #fff6bf;
            box-shadow: 0 0 3px rgba(255,193,7,0.5);
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .page {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
                padding: 0;
                outline: none;
            }

            .editable:hover,
            .editable:focus {
                background: transparent;
                box-shadow: none;
            }
        }
    </style>
</head>
<body data-document-id="{$documentId}">
    <div class="toolbar">
        <button type="button" id="printBtn" onclick="window.print()">
            <i class="fas fa-print"></i> Print / Save PDF
        </button>
        <button type="button" id="editBtn" onclick="toggleEditMode(true)">
            <i class="fas fa-edit"></i> Enable Edit
        </button>
        <button type="button" id="lockBtn" onclick="toggleEditMode(false)" style="display:none;">
            <i class="fas fa-lock"></i> Lock Edit
        </button>
        <button type="button" id="saveBtn" class="save-btn" onclick="saveDocument()" style="display:none;">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <div class="status" id="status" style="display:none;"></div>
    </div>

    {$pageContent}

    <script>
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content || 
                   document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1];
        }

        let isEditing = false;
        const documentId = document.body.getAttribute('data-document-id');

        function toggleEditMode(enable) {
            isEditing = enable;
            const editBtn = document.getElementById('editBtn');
            const lockBtn = document.getElementById('lockBtn');
            const saveBtn = document.getElementById('saveBtn');
            const page = document.querySelector('.page');
            const status = document.getElementById('status');

            document.querySelectorAll('.editable').forEach(el => {
                el.contentEditable = enable;
                if (enable) {
                    el.style.cursor = 'text';
                } else {
                    el.style.cursor = 'default';
                }
            });

            editBtn.style.display = enable ? 'none' : 'block';
            lockBtn.style.display = enable ? 'block' : 'none';
            saveBtn.style.display = enable ? 'block' : 'none';
            page.setAttribute('data-editing', enable);

            if (enable) {
                status.textContent = '✎ Editing Mode Active';
                status.className = 'status editing';
                status.style.display = 'block';
            } else {
                status.style.display = 'none';
            }
        }

        async function saveDocument() {
            if (!isEditing || !documentId) {
                alert('Cannot save. Not in edit mode.');
                return;
            }

            const pageElement = document.querySelector('.page');
            const content = pageElement.innerHTML;
            const status = document.getElementById('status');
            const saveBtn = document.getElementById('saveBtn');
            const originalSaveHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="button-spinner"></span>Saving...';

            try {
                const response = await fetch('/documents/save-edited', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({
                        document_id: documentId,
                        content: content,
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    status.textContent = '✓ Saved successfully';
                    status.className = 'status saved';
                    status.style.display = 'block';
                    
                    setTimeout(() => {
                        status.style.display = 'none';
                    }, 3000);
                } else {
                    alert('Error: ' + (data.message || 'Failed to save'));
                    status.textContent = '✗ Save failed';
                    status.className = 'status';
                    status.style.display = 'block';
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Error saving document: ' + error.message);
                status.textContent = '✗ Save failed';
                status.className = 'status';
                status.style.display = 'block';
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalSaveHtml;
            }
        }

        // Prevent pasting formatted content
        document.addEventListener('paste', function(e) {
            const target = document.activeElement;
            if (target && target.classList.contains('editable')) {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                document.execCommand('insertText', false, text);
            }
        });

        // Prevent unsafe formatting
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') e.preventDefault();
            if (e.ctrlKey && e.key === 'i') e.preventDefault();
            if (e.ctrlKey && e.key === 'u') e.preventDefault();
        });
    </script>
</body>
</html>
HTML;
    }
}
