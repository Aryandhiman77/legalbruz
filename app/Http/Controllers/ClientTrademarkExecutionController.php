<?php

namespace App\Http\Controllers;

use App\Models\StuckTrademarkCase;
use App\Models\TrademarkDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientTrademarkExecutionController extends Controller
{
    public function showExecution(StuckTrademarkCase $case)
    {
        $this->authorizeApplicant($case);

        return view('stuck-trademark.execution', [
            'case' => $case->load([
                'documentRequests',
                'executionUpdates' => fn ($query) => $query->where('visible_to_client', true)->latest(),
                'executionDocuments' => fn ($query) => $query->where('visible_to_client', true)->latest(),
            ]),
        ]);
    }

    public function uploadRequestedDocument(Request $request, StuckTrademarkCase $case, TrademarkDocumentRequest $documentRequest)
    {
        $this->authorizeApplicant($case);
        abort_unless((int) $documentRequest->case_id === (int) $case->id, 404);

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:15360',
        ]);

        $path = $validated['file']->store('execution-request-uploads/' . $case->id, 'public');

        $documentRequest->update([
            'uploaded_file' => $path,
            'status' => 'Uploaded',
        ]);

        return redirect()->route('client.trademark-execution.show', $case)
            ->with('success', 'Requested document uploaded.');
    }

    private function authorizeApplicant(StuckTrademarkCase $case): void
    {
        abort_unless(Auth::id() === $case->user_id, 403);
    }
}
