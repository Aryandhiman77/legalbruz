<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Services\DocumentGenerator;
use App\Services\TrademarkWorkflowService;
use App\Support\TrademarkWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PDF;

class UserDocumentController extends Controller
{
    private function canAccessDocument(Document $document): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return Auth::check() && $document->user_id === Auth::id();
    }

    /**
     * Show user's documents dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $relations = ['documents', 'payments'];

        if (Schema::hasTable('application_tasks')) {
            $relations[] = 'tasks';
        }

        if (Schema::hasTable('draft_versions')) {
            $relations[] = 'draftVersions';
        }

        $applications = $user->applications()->with($relations)->get();

        if (!Schema::hasTable('application_tasks')) {
            $applications->each(fn (Application $application) => $application->setRelation('tasks', collect()));
        }

        if (!Schema::hasTable('draft_versions')) {
            $applications->each(fn (Application $application) => $application->setRelation('draftVersions', collect()));
        }

        $stats = [
            'userApplicationCount' => $applications->count(),
            'approvedCount' => $applications->where('current_status', TrademarkWorkflow::AWAITING_APPROVAL)->count(),
            'totalDocuments' => $applications->sum(fn($app) => $app->documents->count()),
            'verifiedCount' => Document::whereIn('application_id', $applications->pluck('id'))->where('verified_at', '!=', null)->count(),
        ];

        return view('user.documents', array_merge(
            ['applications' => $applications],
            $stats
        ));
    }

    /**
     * View document in browser
     */
    public function view(Document $document)
    {
        if (!$this->canAccessDocument($document)) {
            abort(403);
        }

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Document not found');
        }

        return response()->file($filePath);
    }

    /**
     * Download document as PDF
     */
    public function download(Document $document)
    {
        if (!$this->canAccessDocument($document)) {
            abort(403);
        }

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Document not found');
        }

        // If it's an HTML file, convert to PDF
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'html') {
            $htmlContent = file_get_contents($filePath);
            
            // Use DomPDF to convert HTML to PDF
            $pdf = \PDF::loadHTML($htmlContent);
            $pdf->setPaper('A4', 'portrait');
            
            // Generate filename
            $pdfFilename = str_replace('.html', '.pdf', $document->file_name);
            
            return $pdf->download($pdfFilename);
        }
        
        // For other files, download directly
        return response()->download(
            $filePath,
            $document->file_name
        );
    }

    /**
     * Upload signed document
     */
    public function uploadSigned(Request $request, TrademarkWorkflowService $workflow)
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
            'application_id' => 'required|exists:applications,id',
            'signed_document' => 'required|file|max:10240', // 10MB
            'signature_notes' => 'nullable|string|max:1000',
        ]);

        $document = Document::findOrFail($validated['document_id']);
        $application = Application::findOrFail($validated['application_id']);

        // Verify user owns this application
        if ($application->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // 1. Delete any previously uploaded signed versions (old uploads for same document type)
            $previousSignedDocs = Document::where('application_id', $application->id)
                ->where('document_type', $document->document_type . ' (Signed)')
                ->get();
            
            foreach ($previousSignedDocs as $oldDoc) {
                // Delete file from storage
                if (Storage::disk('public')->exists($oldDoc->file_path)) {
                    Storage::disk('public')->delete($oldDoc->file_path);
                }
                // Delete database record
                $oldDoc->delete();
            }

            // 2. Store new signed document
            $file = $request->file('signed_document');
            $filename = 'signed-' . $document->document_type . '-' . $application->id . '-' . now()->timestamp . '.' . $file->getClientOriginalExtension();
            $path = 'documents/signed/' . $filename;

            Storage::disk('public')->put($path, file_get_contents($file));

            // 3. Create new document record for signed version
            $signedDoc = Document::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'document_type' => $document->document_type . ' (Signed)',
                'file_path' => $path,
                'file_name' => $filename,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'status' => 'uploaded',
                'verification_notes' => $validated['signature_notes'] ?? 'User uploaded signed document',
            ]);

            // 4. Update original document status to archived (hidden from user view)
            $document->update([
                'status' => 'archived'
            ]);

            if ($document->document_type === 'engagement_letter') {
                $workflow->completeTask($application, 'engagement_letter_signed');
            }

            if ($document->document_type === 'poa') {
                $workflow->completeTask($application, 'poa_signed');
            }

            $workflow->refreshOnboardingStatus($application);

            return response()->json([
                'success' => true,
                'message' => 'Signed document uploaded successfully. Admin will review it shortly.',
                'document' => $signedDoc
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a user's signature for an approved application.
     */
    public function submitSignature(Request $request, $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($application->current_status !== TrademarkWorkflow::ONBOARDING_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Signature can only be submitted during onboarding.'
            ], 422);
        }

        $validated = $request->validate([
            'signature_mode' => 'required|in:digital,image',
            'digital_signature' => 'nullable|string|max:255|required_if:signature_mode,digital',
            'signature_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120|required_if:signature_mode,image',
            'signature_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $existingSignatureDocs = Document::where('application_id', $application->id)
                ->where('document_type', 'signature')
                ->get();

            foreach ($existingSignatureDocs as $oldDoc) {
                if ($oldDoc->file_path && Storage::disk('public')->exists($oldDoc->file_path)) {
                    Storage::disk('public')->delete($oldDoc->file_path);
                }

                $oldDoc->delete();
            }

            if ($validated['signature_mode'] === 'digital') {
                $signatureText = trim($validated['digital_signature']);
                $filename = 'digital-signature-' . $application->id . '-' . now()->timestamp . '.html';
                $path = 'documents/signatures/' . $filename;

                $html = view('user.partials.signature-document', [
                    'application' => $application,
                    'signatureText' => $signatureText,
                    'signatureNotes' => $validated['signature_notes'] ?? null,
                    'submittedAt' => now(),
                    'user' => Auth::user(),
                ])->render();

                Storage::disk('public')->put($path, $html);

                $fileName = $filename;
                $fileType = 'html';
                $fileSize = strlen($html);
                $verificationNotes = 'Digital signature submitted by user: ' . $signatureText;
            } else {
                $file = $request->file('signature_image');
                $filename = 'signature-image-' . $application->id . '-' . now()->timestamp . '.' . $file->getClientOriginalExtension();
                $path = 'documents/signatures/' . $filename;

                Storage::disk('public')->put($path, file_get_contents($file));

                $fileName = $filename;
                $fileType = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $verificationNotes = 'Signature image uploaded by user.';
            }

            if (!empty($validated['signature_notes'])) {
                $verificationNotes .= ' Notes: ' . $validated['signature_notes'];
            }

            $signatureDocument = Document::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'document_type' => 'signature',
                'file_path' => $path,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'status' => 'uploaded',
                'verification_notes' => $verificationNotes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Signature submitted successfully. Our team will review it shortly.',
                'document' => $signatureDocument,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit signature: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitAffidavit(Request $request, $applicationId, TrademarkWorkflowService $workflow)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('trademark.status', $application->id)
            ->with('info', 'The affidavit is provided for download only and does not need to be signed or submitted.');
    }

    /**
     * List user's documents (API endpoint)
     */
    public function listDocuments()
    {
        $user = Auth::user();
        $documents = Document::whereIn(
            'application_id',
            $user->applications()->pluck('id')
        )->get();

        return response()->json($documents);
    }
}
