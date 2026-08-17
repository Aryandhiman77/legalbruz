<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Payment;
use App\Models\TrademarkPricing;
use App\Services\TrademarkWorkflowService;
use App\Support\TrademarkWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrademarkController extends Controller
{
    private const GST_NUMBER_REGEX = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
    private const MOBILE_NUMBER_REGEX = '/^[6789]\d{9}$/';
    private const PINCODE_REGEX = '/^\d{6}$/';

    /**
     * Show trademark type selection
     */
    public function showTypeSelection()
    {
        return view('trademark.type-selection');
    }

    /**
     * Show KYC checklist based on entity type
     */
    public function showKycChecklist($type)
    {
        $kycRequirements = [
            'individual' => [
                'PAN Card',
                'Address Proof',
                'Email & Phone',
            ],
            'company' => [
                'Certificate of Incorporation',
                'PAN',
                'GST (if available)',
                'Authorized Signatory ID Proof',
            ]
        ];

        return view('trademark.kyc-checklist', [
            'type' => $type,
            'requirements' => $kycRequirements[$type] ?? []
        ]);
    }

    /**
     * Show application form
     */
    public function showApplicationForm($type)
    {
        return view('trademark.application-form', ['entity_type' => $type]);
    }

    /**
     * Store trademark application
     */
    public function storeApplication(Request $request, TrademarkWorkflowService $workflow)
    {
        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_address' => 'required|string|max:500',
            'billing_email' => 'required|email|max:255',
            'billing_mobile' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'gst_number' => ['nullable', 'string', 'max:15', 'regex:' . self::GST_NUMBER_REGEX],

            'applicant_name' => 'required|string|max:255',
            'applicant_address' => 'required|string|max:500',
            'applicant_district' => 'required|string|max:255',
            'applicant_state' => 'required|string|max:255',
            'applicant_pincode' => ['required', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'applicant_phone' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'applicant_email' => 'required|email|max:255',
            'type_of_applicant' => 'required|in:individual,company,llp,ngo,small_enterprise,startup,others',

            'signatory_name' => 'required|string|max:255',
            'signatory_father_name' => 'required|string|max:255',
            'signatory_address' => 'required|string|max:500',
            'signatory_district' => 'required|string|max:255',
            'signatory_state' => 'required|string|max:255',
            'signatory_pincode' => ['required', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'signatory_phone' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'signatory_email' => 'required|email|max:255',
            'signatory_designation' => 'required|in:director,partner,proprietor,authorised_signatory',

            'co_applicant_name' => 'nullable|string|max:255',
            'co_applicant_father_name' => 'nullable|string|max:255',
            'co_applicant_address' => 'nullable|string|max:500',
            'co_applicant_state' => 'nullable|string|max:255',
            'co_applicant_district' => 'nullable|string|max:255',
            'co_applicant_pincode' => ['nullable', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'co_applicant_mobile' => ['nullable', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'co_applicant_email' => 'nullable|email|max:255',
            'co_applicant_designation' => 'nullable|in:co_applicant,partner',

            'trademark_type' => 'required|in:word,device,shape_of_goods,colour,sound_mark,three_dimensional,taste_mark,smell_mark',
            'mark_brand' => 'required|string|max:255',
            'trademark_language' => 'required|string|max:255',
            'trademark_origin_description' => 'nullable|string|max:1000',
            'mark_conditions' => 'nullable|string|max:1000',
            'trademark_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'goods_services' => 'required|string|max:2000',
            'trade_description' => 'required|in:manufacturer,trader,service_provider',
            'trademark_usage_status' => 'required|in:used,proposed',
            'trademark_use_date' => 'nullable|date',
            'proof_of_use' => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
            'application_type' => 'required|in:trademark,certification,collective,series',
        ], $this->validationMessages());

        $entityType = $validated['type_of_applicant'] === 'individual' ? 'individual' : 'company';
        $billingAddress = trim($validated['billing_address']);
        $applicantFullAddress = trim(
            $validated['applicant_address'] . ', ' .
            $validated['applicant_district'] . ', ' .
            $validated['applicant_state'] . ' - ' .
            $validated['applicant_pincode']
        );
        $logoPath = null;
        $proofOfUsePath = null;

        if ($request->hasFile('trademark_image')) {
            $logoPath = $request->file('trademark_image')->store('logos', 'public');
        }

        if ($request->hasFile('proof_of_use')) {
            $proofOfUsePath = $request->file('proof_of_use')->store('proof-of-use', 'public');
        }

        $application = Application::create([
            'user_id' => Auth::id(),
            'type' => $validated['application_type'],
            'entity_type' => $entityType,
            'applicant_name' => $validated['applicant_name'],
            'phone' => $validated['applicant_phone'],
            'email' => $validated['applicant_email'],
            'brand_name' => $validated['mark_brand'],
            'logo_path' => $logoPath,
            'description' => $validated['trademark_origin_description'] ?? null,
            'industry' => $validated['trade_description'],
            'usage_type' => 'india',
            'first_use_date' => $validated['trademark_usage_status'] === 'used'
                ? ($validated['trademark_use_date'] ?? null)
                : null,
            'currently_selling' => $validated['trademark_usage_status'] === 'used',
            'address' => $applicantFullAddress,
            'goods_services' => $validated['goods_services'],
            'usage' => $validated['trademark_usage_status'] === 'used' ? 'used' : 'proposed',
            'members_details' => [
                'billing_company_details' => [
                    'billing_name' => $validated['billing_name'],
                    'billing_address' => $billingAddress,
                    'billing_email' => $validated['billing_email'],
                    'billing_mobile' => $validated['billing_mobile'],
                    'gst_number' => $validated['gst_number'] ?? null,
                ],
                'trademark_applicant_details' => [
                    'applicant_name' => $validated['applicant_name'],
                    'address_of_applicant' => $validated['applicant_address'],
                    'district' => $validated['applicant_district'],
                    'state' => $validated['applicant_state'],
                    'pin_code' => $validated['applicant_pincode'],
                    'phone_mobile_number' => $validated['applicant_phone'],
                    'email_id' => $validated['applicant_email'],
                    'type_of_applicant' => $validated['type_of_applicant'],
                ],
                'details_of_signatory' => [
                    'name_of_signatory' => $validated['signatory_name'],
                    'fathers_name' => $validated['signatory_father_name'],
                    'address_of_ar_signatory' => $validated['signatory_address'],
                    'district' => $validated['signatory_district'],
                    'state' => $validated['signatory_state'],
                    'pin_code' => $validated['signatory_pincode'],
                    'phone_mobile_number' => $validated['signatory_phone'],
                    'email_id' => $validated['signatory_email'],
                    'designation_of_signatory' => $validated['signatory_designation'],
                ],
                'details_of_co_applicant_or_partners' => [
                    'name_of_co_applicant_partner' => $validated['co_applicant_name'] ?? null,
                    'fathers_name' => $validated['co_applicant_father_name'] ?? null,
                    'address' => $validated['co_applicant_address'] ?? null,
                    'state' => $validated['co_applicant_state'] ?? null,
                    'district' => $validated['co_applicant_district'] ?? null,
                    'pin_code' => $validated['co_applicant_pincode'] ?? null,
                    'mobile_number' => $validated['co_applicant_mobile'] ?? null,
                    'email_id' => $validated['co_applicant_email'] ?? null,
                    'designation' => $validated['co_applicant_designation'] ?? null,
                ],
                'trademark_details' => [
                    'trademark_type' => $validated['trademark_type'],
                    'mark_brand_in_words' => $validated['mark_brand'],
                    'language_of_trademark' => $validated['trademark_language'],
                    'origin_of_trademark' => $validated['trademark_origin_description'] ?? null,
                    'conditions_or_limitations' => $validated['mark_conditions'] ?? null,
                    'image_of_trademark' => $logoPath,
                    'goods_or_services' => $validated['goods_services'],
                    'trade_description' => $validated['trade_description'],
                    'user_date_or_proposed' => $validated['trademark_usage_status'],
                    'trademark_use_date' => $validated['trademark_use_date'] ?? null,
                    'proof_of_use_of_trademark' => $proofOfUsePath,
                    'application_type' => $validated['application_type'],
                ],
            ],
            'status' => 'payment_pending',
        ]);

        $workflow->initialize($application);

        return redirect()->route('payment.show', $application->id)
            ->with('success', 'Application created. Please complete 50% payment.');
    }

    /**
     * Show payment page with requirements
     */
    public function showPayment($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $payment = $application->payments()->where('status', 'completed')->first();
        if ($payment) {
            return redirect()->route('trademark.status', $application->id)
                ->with('info', 'Payment already completed. Your application is with the admin team.');
        }

        $totalAmount = TrademarkPricing::amountForApplicantType($application->entity_type);
        $amount = round($totalAmount * 0.5);

        return view('trademark.payment', [
            'application' => $application,
            'amount' => $amount,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Show detailed application form
     */
    public function showDetailedForm($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('trademark.status', $application->id)
            ->with('error', 'Application editing is disabled after payment.');
    }

    /**
     * Store detailed form
     */
    public function storeDetailedForm(Request $request, $applicationId, TrademarkWorkflowService $workflow)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('trademark.status', $application->id)
            ->with('error', 'Application editing is disabled after payment.');

        if (!in_array($application->current_status, [TrademarkWorkflow::APPLICATION_SUBMITTED], true)) {
            return redirect()->route('trademark.status', $application->id)
                ->with('error', 'Application editing is disabled after it has been submitted for admin review.');
        }

        $existingDetails = $application->members_details ?? [];
        $existingTrademarkDetails = $existingDetails['trademark_details'] ?? [];
        $existingTrademarkImagePath = $application->logo_path ?: ($existingTrademarkDetails['image_of_trademark'] ?? null);
        $existingProofOfUsePath = $existingTrademarkDetails['proof_of_use_of_trademark'] ?? null;
        $hasTrademarkImage = filled($existingTrademarkImagePath)
            && Storage::disk('public')->exists($this->normalizePublicStoragePath((string) $existingTrademarkImagePath));
        $hasProofOfUse = filled($existingProofOfUsePath)
            && Storage::disk('public')->exists($this->normalizePublicStoragePath((string) $existingProofOfUsePath));

        $validated = $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_address' => 'required|string|max:500',
            'billing_email' => 'required|email|max:255',
            'billing_mobile' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'gst_number' => ['nullable', 'string', 'max:15', 'regex:' . self::GST_NUMBER_REGEX],

            'applicant_name' => 'required|string|max:255',
            'applicant_address' => 'required|string|max:500',
            'applicant_district' => 'required|string|max:255',
            'applicant_state' => 'required|string|max:255',
            'applicant_pincode' => ['required', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'applicant_phone' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'applicant_email' => 'required|email|max:255',
            'type_of_applicant' => 'required|in:individual,company,llp,ngo,small_enterprise,startup,others',

            'signatory_name' => 'required|string|max:255',
            'signatory_father_name' => 'required|string|max:255',
            'signatory_address' => 'required|string|max:500',
            'signatory_district' => 'required|string|max:255',
            'signatory_state' => 'required|string|max:255',
            'signatory_pincode' => ['required', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'signatory_phone' => ['required', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'signatory_email' => 'required|email|max:255',
            'signatory_designation' => 'required|in:director,partner,proprietor,authorised_signatory',

            'co_applicant_name' => 'nullable|string|max:255',
            'co_applicant_father_name' => 'nullable|string|max:255',
            'co_applicant_address' => 'nullable|string|max:500',
            'co_applicant_state' => 'nullable|string|max:255',
            'co_applicant_district' => 'nullable|string|max:255',
            'co_applicant_pincode' => ['nullable', 'string', 'size:6', 'regex:' . self::PINCODE_REGEX],
            'co_applicant_mobile' => ['nullable', 'string', 'size:10', 'regex:' . self::MOBILE_NUMBER_REGEX],
            'co_applicant_email' => 'nullable|email|max:255',
            'co_applicant_designation' => 'nullable|in:co_applicant,partner',

            'trademark_type' => 'required|in:word,device,shape_of_goods,colour,sound_mark,three_dimensional,taste_mark,smell_mark',
            'mark_brand' => 'required|string|max:255',
            'trademark_language' => 'required|string|max:255',
            'trademark_origin_description' => 'nullable|string|max:1000',
            'mark_conditions' => 'nullable|string|max:1000',
            'trademark_image' => ($hasTrademarkImage ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,webp|max:4096',
            'goods_services' => 'required|string|max:2000',
            'trade_description' => 'required|in:manufacturer,trader,service_provider',
            'trademark_usage_status' => 'required|in:used,proposed',
            'trademark_use_date' => 'nullable|date',
            'proof_of_use' => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
            'application_type' => 'required|in:trademark,certification,collective,series',
        ], $this->validationMessages());

        $entityType = $validated['type_of_applicant'] === 'individual' ? 'individual' : 'company';
        $billingAddress = trim($validated['billing_address']);
        $applicantFullAddress = trim(
            $validated['applicant_address'] . ', ' .
            $validated['applicant_district'] . ', ' .
            $validated['applicant_state'] . ' - ' .
            $validated['applicant_pincode']
        );

        $logoPath = $application->logo_path;
        if ($request->hasFile('trademark_image')) {
            $logoPath = $request->file('trademark_image')->store('logos', 'public');
        }

        $proofOfUsePath = $existingTrademarkDetails['proof_of_use_of_trademark'] ?? null;
        if ($request->hasFile('proof_of_use')) {
            $proofOfUsePath = $request->file('proof_of_use')->store('proof-of-use', 'public');
        }

        $application->update([
            'applicant_name' => $validated['applicant_name'],
            'entity_type' => $entityType,
            'type' => $validated['application_type'],
            'phone' => $validated['applicant_phone'],
            'email' => $validated['applicant_email'],
            'brand_name' => $validated['mark_brand'],
            'logo_path' => $logoPath,
            'description' => $validated['trademark_origin_description'] ?? null,
            'industry' => $validated['trade_description'],
            'usage_type' => 'india',
            'first_use_date' => $validated['trademark_usage_status'] === 'used'
                ? ($validated['trademark_use_date'] ?? null)
                : null,
            'currently_selling' => $validated['trademark_usage_status'] === 'used',
            'address' => $applicantFullAddress,
            'goods_services' => $validated['goods_services'],
            'usage' => $validated['trademark_usage_status'] === 'used' ? 'used' : 'proposed',
            'members_details' => [
                'billing_company_details' => [
                    'billing_name' => $validated['billing_name'],
                    'billing_address' => $billingAddress,
                    'billing_email' => $validated['billing_email'],
                    'billing_mobile' => $validated['billing_mobile'],
                    'gst_number' => $validated['gst_number'] ?? null,
                ],
                'trademark_applicant_details' => [
                    'applicant_name' => $validated['applicant_name'],
                    'address_of_applicant' => $validated['applicant_address'],
                    'district' => $validated['applicant_district'],
                    'state' => $validated['applicant_state'],
                    'pin_code' => $validated['applicant_pincode'],
                    'phone_mobile_number' => $validated['applicant_phone'],
                    'email_id' => $validated['applicant_email'],
                    'type_of_applicant' => $validated['type_of_applicant'],
                ],
                'details_of_signatory' => [
                    'name_of_signatory' => $validated['signatory_name'],
                    'fathers_name' => $validated['signatory_father_name'],
                    'address_of_ar_signatory' => $validated['signatory_address'],
                    'district' => $validated['signatory_district'],
                    'state' => $validated['signatory_state'],
                    'pin_code' => $validated['signatory_pincode'],
                    'phone_mobile_number' => $validated['signatory_phone'],
                    'email_id' => $validated['signatory_email'],
                    'designation_of_signatory' => $validated['signatory_designation'],
                ],
                'details_of_co_applicant_or_partners' => [
                    'name_of_co_applicant_partner' => $validated['co_applicant_name'] ?? null,
                    'fathers_name' => $validated['co_applicant_father_name'] ?? null,
                    'address' => $validated['co_applicant_address'] ?? null,
                    'state' => $validated['co_applicant_state'] ?? null,
                    'district' => $validated['co_applicant_district'] ?? null,
                    'pin_code' => $validated['co_applicant_pincode'] ?? null,
                    'mobile_number' => $validated['co_applicant_mobile'] ?? null,
                    'email_id' => $validated['co_applicant_email'] ?? null,
                    'designation' => $validated['co_applicant_designation'] ?? null,
                ],
                'trademark_details' => [
                    'trademark_type' => $validated['trademark_type'],
                    'mark_brand_in_words' => $validated['mark_brand'],
                    'language_of_trademark' => $validated['trademark_language'],
                    'origin_of_trademark' => $validated['trademark_origin_description'] ?? null,
                    'conditions_or_limitations' => $validated['mark_conditions'] ?? null,
                    'image_of_trademark' => $logoPath,
                    'goods_or_services' => $validated['goods_services'],
                    'trade_description' => $validated['trade_description'],
                    'user_date_or_proposed' => $validated['trademark_usage_status'],
                    'trademark_use_date' => $validated['trademark_use_date'] ?? null,
                    'proof_of_use_of_trademark' => $proofOfUsePath,
                    'application_type' => $validated['application_type'],
                ],
            ],
        ]);

        $reviewNoteUpdates = ['rejection_reason' => null];
        if (Schema::hasColumn('applications', 'admin_review_note')) {
            $reviewNoteUpdates['admin_review_note'] = null;
        }
        $application->forceFill($reviewNoteUpdates)->save();

        $workflow->submitForReview($application->fresh());

        return redirect()->route('trademark.status', $application->id)
            ->with('success', 'Your application has been submitted for admin review.');
    }

    /**
     * Show document upload page
     */
    public function showDocumentUpload($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect(route('trademark.status', ['id' => $application->id, 'stage_action' => 1]) . '#stage-action');
    }

    /**
     * Store uploaded documents
     */
    public function storeDocuments(Request $request, $applicationId, TrademarkWorkflowService $workflow)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key) && $key !== '_token') {
                $file = $request->file($key);
                $path = $file->store('documents/' . $application->id, 'public');

                Document::create([
                    'application_id' => $application->id,
                    'user_id' => Auth::id(),
                    'document_type' => $key,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                ]);
            }
        }

        $workflow->refreshOnboardingStatus($application);

        return redirect()->route('trademark.status', $application->id)
            ->with('success', 'Documents uploaded successfully.');
    }

    /**
     * Show document download page
     */
    public function showDocumentDownload($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return view('trademark.document-download', ['applicationId' => $applicationId]);
    }

    /**
     * Show application status
     */
    public function showStatus($applicationId)
    {
        $application = Application::with($this->applicationRelations())->findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $application = $this->normalizeApplicationRelations($application);

        return view('trademark.status', ['application' => $application]);
    }

    /**
     * Show previously submitted application data and documents for a workflow stage.
     */
    public function showStageDetails($applicationId, string $stage)
    {
        $application = Application::with($this->applicationRelations())->findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $application = $this->normalizeApplicationRelations($application);

        return view('trademark.stage-details', [
            'application' => $application,
            'stage' => $stage,
        ]);
    }

    /**
     * View stored trademark image
     */
    public function viewTrademarkImage(Request $request, $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->canViewApplicationFile($application)) {
            abort(403);
        }

        $imagePath = $this->resolvePublicFilePath([
            $this->decodedFileQuery($request),
            data_get($application->members_details, 'trademark_details.image_of_trademark'),
            $application->logo_path,
        ], ['logos']);

        if (!$imagePath) {
            abort(404, 'Trademark image file is missing from storage. Please re-upload the trademark image from the application details form.');
        }

        return response()->file($imagePath);
    }

    /**
     * View stored proof of use file
     */
    public function viewProofOfUse(Request $request, $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if (!$this->canViewApplicationFile($application)) {
            abort(403);
        }

        $proofOfUsePath = $this->resolvePublicFilePath([
            $this->decodedFileQuery($request),
            data_get($application->members_details, 'trademark_details.proof_of_use_of_trademark'),
        ], ['proof-of-use']);

        if (!$proofOfUsePath) {
            abort(404, 'Proof of use file not found.');
        }

        return response()->file($proofOfUsePath);
    }

    private function canViewApplicationFile(Application $application): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return Auth::check() && $application->user_id === Auth::id();
    }

    private function resolvePublicFilePath(array $paths, array $fallbackDirectories = []): ?string
    {
        foreach ($paths as $path) {
            if (!is_string($path) || trim($path) === '') {
                continue;
            }

            $absolutePath = $this->resolveAbsolutePublicFilePath($path);

            if ($absolutePath) {
                return $absolutePath;
            }

            $normalizedPath = $this->normalizePublicStoragePath($path);

            if ($normalizedPath && Storage::disk('public')->exists($normalizedPath)) {
                return Storage::disk('public')->path($normalizedPath);
            }

            $publicPath = $this->resolvePublicWebFilePath($normalizedPath);

            if ($publicPath) {
                return $publicPath;
            }

            $fallbackPath = $this->resolveFallbackPublicStoragePath($normalizedPath, $fallbackDirectories);

            if ($fallbackPath) {
                return $fallbackPath;
            }
        }

        return null;
    }

    private function decodedFileQuery(Request $request): ?string
    {
        $encodedPath = $request->query('file');

        if (!is_string($encodedPath) || $encodedPath === '') {
            return null;
        }

        $decodedPath = base64_decode($encodedPath, true);

        return is_string($decodedPath) && $decodedPath !== '' ? $decodedPath : null;
    }

    private function resolveAbsolutePublicFilePath(string $path): ?string
    {
        $urlPath = parse_url(trim($path), PHP_URL_PATH);
        $path = rawurldecode($urlPath ?: $path);

        if (!str_starts_with($path, '/')) {
            return null;
        }

        $realPath = realpath($path);
        $publicRoot = realpath(Storage::disk('public')->path(''));

        if (!$realPath || !$publicRoot) {
            return null;
        }

        $publicRoot = rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($realPath, $publicRoot) ? $realPath : null;
    }

    private function normalizePublicStoragePath(string $path): string
    {
        $urlPath = parse_url(trim($path), PHP_URL_PATH);
        $path = $urlPath ?: $path;
        $path = rawurldecode(str_replace('\\', '/', $path));
        $path = preg_replace('#/+#', '/', $path);
        $path = ltrim($path, '/');

        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/storage/',
            'storage/',
        ] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return substr($path, strlen($prefix));
            }
        }

        return $path;
    }

    private function resolvePublicWebFilePath(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        $realPath = realpath(public_path($path));
        $publicRoot = realpath(public_path());

        if (!$realPath || !$publicRoot || !is_file($realPath)) {
            return null;
        }

        $publicRoot = rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($realPath, $publicRoot) ? $realPath : null;
    }

    private function resolveFallbackPublicStoragePath(string $path, array $directories): ?string
    {
        $filename = basename($path);

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        foreach ($directories as $directory) {
            $candidate = trim($directory, '/') . '/' . $filename;

            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }
        }

        return null;
    }

    /**
     * Generate and download Affidavit
     */
    public function downloadAffidavit($applicationId)
    {
        $application = Application::with('user')->findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $html = $this->generateAffidavitHTML($application);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Affidavit_' . $application->id . '_' . date('Ymd') . '.html"');
    }

    /**
     * Generate and download Power of Attorney (POA)
     */
    public function downloadPOA($applicationId)
    {
        $application = Application::with('user')->findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $html = $this->generatePOAHTML($application);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="POA_' . $application->id . '_' . date('Ymd') . '.html"');
    }

    /**
     * Generate Affidavit HTML
     */
    private function generateAffidavitHTML($application)
    {
        $user = $application->user;
        $date = now()->format('d.m.Y');
        $firstUseDate = $application->first_use_date ?? 'N/A';

        return <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
                    .header { text-align: center; font-weight: bold; margin-bottom: 30px; }
                    .title { font-size: 16px; font-weight: bold; text-align: center; margin: 20px 0; }
                    .content { text-align: justify; margin: 20px 0; }
                    .signature-section { margin-top: 60px; }
                    .signature-line { margin-top: 40px; display: inline-block; width: 250px; border-top: 1px solid black; text-align: center; }
                    .box { border: 1px solid black; padding: 20px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>AFFIDAVIT</h2>
                </div>

                <div class="box">
                    <p><strong>AFFIDAVIT FOR TRADEMARK REGISTRATION</strong></p>

                    <div class="content">
HTML;

        $html .= '<p>I, <strong>' . htmlspecialchars($user->name) . '</strong>, Son/Daughter/Wife of ______________, ';
        $html .= 'resident of ______________, do hereby solemnly affirm and declare as follows:</p>';

        $html .= '<p><strong>1.</strong> That I am the applicant for Trademark Registration bearing ';
        $html .= 'Application No. <strong>' . htmlspecialchars($application->id) . '</strong>.</p>';

        $html .= '<p><strong>2.</strong> That the Brand/Trademark proposed to be registered is ';
        $html .= '<strong>"' . htmlspecialchars($application->brand_name) . '"</strong>.</p>';

        $html .= <<<'HTML'
                        <p><strong>3.</strong> That I have the rights to use this trademark and am the true
                        owner of the same.</p>

                        <p><strong>4.</strong> That the information provided in the application is true
                        and correct to the best of my knowledge and belief.</p>

                        <p><strong>5.</strong> That I shall use the said mark in connection with the goods/services
                        specified in the application.</p>

                        <p><strong>6.</strong> That no false information has been furnished in this application.</p>

                        <p><strong>7.</strong> That the Trademark has been first used in connection with the
                        goods/services on <strong>
HTML;

        $html .= htmlspecialchars($firstUseDate) . '</strong>.</p>';

        $html .= <<<'HTML'

                        <p>I solemnly declare that the contents of this Affidavit are true to the best of
                        my knowledge and belief. I am well acquainted with the facts stated herein.
                        I have not concealed any material fact.</p>
                    </div>
                </div>

                <div class="signature-section">
                    <p><strong>Affiant's Name:</strong>
HTML;

        $html .= htmlspecialchars($user->name) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . htmlspecialchars($date) . '</p>';

        $html .= <<<'HTML'
                    <p><strong>Place:</strong> ____________________</p>

                    <div class="signature-line">
                        Signature of Affiant
                    </div>
                </div>

                <div style="text-align: center; margin-top: 60px; border-top: 1px solid black; padding-top: 20px;">
                    <p><strong>BEFORE ME:</strong></p>
                    <div class="signature-line">
                        Notary / First Class Magistrate
                    </div>
                </div>
            </body>
            </html>
HTML;

        return $html;
    }

    /**
     * Generate POA HTML
     */
    private function generatePOAHTML($application)
    {
        $user = $application->user;
        $date = now()->format('d.m.Y');

        $html = <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
                    .header { text-align: center; font-weight: bold; margin-bottom: 30px; }
                    .title { font-size: 16px; font-weight: bold; text-align: center; margin: 20px 0; }
                    .content { text-align: justify; margin: 20px 0; }
                    .signature-section { margin-top: 60px; }
                    .signature-line { margin-top: 40px; display: inline-block; width: 250px; border-top: 1px solid black; text-align: center; }
                    .box { border: 1px solid black; padding: 20px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>POWER OF ATTORNEY</h2>
                </div>

                <div class="box">
                    <p><strong>POWER OF ATTORNEY FOR TRADEMARK REGISTRATION AND REPRESENTATION</strong></p>

                    <div class="content">
HTML;

        $html .= '<p>I, <strong>' . htmlspecialchars($user->name) . '</strong>, resident of ______________, ';
        $html .= 'do hereby authorize and appoint <strong>Legal Bruz  LLP (Law Firm)</strong>, ';
        $html .= 'having their office at 34 Krishna Nagar, Ambala Cantt, Haryana 133001, India, ';
        $html .= 'to act as my Attorney in the matter of registration of Trademark bearing ';
        $html .= 'Application No. <strong>' . htmlspecialchars($application->id) . '</strong>.</p>';

        $html .= <<<'HTML'

                        <p><strong>1. SCOPE OF AUTHORITY:</strong></p>
                        <p>I hereby authorize my said Attorney to:</p>
                        <ul>
                            <li>File the Trademark application with appropriate authorities</li>
                            <li>Appear in proceedings before the Trademark Registry</li>
                            <li>Make submissions and arguments on my behalf</li>
                            <li>Reply to all official communications</li>
                            <li>Execute necessary documents and affidavits</li>
                            <li>Conduct negotiations and correspondence</li>
                            <li>Receive all official letters and certificates</li>
                            <li>Withdraw or amend the application if necessary</li>
                            <li>Perform all acts necessary for the prosecution of the application</li>
                        </ul>

                        <p><strong>2. REPRESENTATION:</strong></p>
                        <p>The said Attorney is hereby authorized to represent me in all matters related
                        to Trademark Registration (Application No.
HTML;

        $html .= htmlspecialchars($application->id) . ') for the brand ';
        $html .= '<strong>"' . htmlspecialchars($application->brand_name) . '"</strong>.</p>';

        $html .= <<<'HTML'

                        <p><strong>3. RATIFICATION:</strong></p>
                        <p>I hereby ratify and confirm all acts and deeds done by my said Attorney
                        in relation to the above matter.</p>

                        <p><strong>4. REVOCATION:</strong></p>
                        <p>All previous authorizations, if any, in respect of this matter are hereby
                        revoked and superseded by this Power of Attorney.</p>

                        <p><strong>5. VALIDITY:</strong></p>
                        <p>This Power of Attorney shall remain valid until the matter is finally
                        disposed of or until revoked by me in writing.</p>
                    </div>
                </div>

                <div class="signature-section">
                    <p><strong>Constituting Attorney Name:</strong>
HTML;

        $html .= htmlspecialchars($user->name) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . htmlspecialchars($date) . '</p>';

        $html .= <<<'HTML'
                    <p><strong>Place:</strong> ____________________</p>

                    <div class="signature-line">
                        Signature of Constituting Attorney
                    </div>
                </div>

                <div style="text-align: center; margin-top: 60px; border-top: 1px solid black; padding-top: 20px;">
                    <p><strong>BEFORE ME:</strong></p>
                    <div class="signature-line">
                        Notary / First Class Magistrate
                    </div>
                </div>
            </body>
            </html>
HTML;

        return $html;
    }

    private function applicationRelations(): array
    {
        $relations = ['documents', 'payments', 'user'];

        if (Schema::hasTable('application_tasks')) {
            $relations[] = 'tasks';
        }

        if (Schema::hasTable('draft_versions')) {
            $relations[] = 'draftVersions';
        }

        if (Schema::hasTable('application_status_logs')) {
            $relations[] = 'statusLogs';
        }

        return $relations;
    }

    private function validationMessages(): array
    {
        return [
            'billing_mobile.regex' => 'Enter a valid Indian mobile number.',
            'applicant_phone.regex' => 'Enter a valid Indian mobile number.',
            'signatory_phone.regex' => 'Enter a valid Indian mobile number.',
            'co_applicant_mobile.regex' => 'Enter a valid Indian mobile number.',
            'applicant_pincode.regex' => 'Enter a valid 6-digit pincode.',
            'signatory_pincode.regex' => 'Enter a valid 6-digit pincode.',
            'co_applicant_pincode.regex' => 'Enter a valid 6-digit pincode.',
            'gst_number.regex' => 'Enter a valid GST number.',
            'trademark_image.required' => 'Please upload the image of the trademark.',
            'proof_of_use.required' => 'Please upload proof of use of the trademark.',
        ];
    }

    private function normalizeApplicationRelations(Application $application): Application
    {
        if (!Schema::hasTable('application_tasks')) {
            $application->setRelation('tasks', collect());
        }

        if (!Schema::hasTable('draft_versions')) {
            $application->setRelation('draftVersions', collect());
        }

        if (!Schema::hasTable('application_status_logs')) {
            $application->setRelation('statusLogs', collect());
        }

        return $application;
    }
}
