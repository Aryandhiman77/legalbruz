<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\DiscountCoupon;
use App\Models\Payment;
use App\Services\TrademarkWorkflowService;
use App\Support\TrademarkWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    /**
     * Show payment page
     */
    public function showPayment($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $originalTotalAmount = $application->entity_type === 'individual' ? 7000 : 9000;
        $autoApplyCoupon = DiscountCoupon::autoApplyForPayment('trademark_filing', Auth::id());
        $totalAmount = $autoApplyCoupon
            ? $autoApplyCoupon->discountedAmountFor($originalTotalAmount)
            : $originalTotalAmount;
        $advanceAmount = round($totalAmount * 0.50);
        $paidServiceQuery = $application->payments()->whereIn('status', ['completed', 'approved']);

        if ($this->hasPaymentsColumn('payment_type')) {
            $paidServiceQuery->whereIn('payment_type', ['advance', 'full']);
        }

        $paidServiceAmount = (float) $paidServiceQuery->sum('amount');
        $finalAmount = max($totalAmount - $paidServiceAmount, 0);
        $paymentType = $application->current_status === TrademarkWorkflow::PAYMENT_PENDING_FINAL ? 'final' : 'advance';

        if ($paymentType === 'final' && $finalAmount <= 0) {
            return redirect()->route('trademark.status', $application->id)
                ->with('info', 'Your final balance is already paid.');
        }

        return view('payments.razorpay-form', [
            'application' => $application,
            'totalAmount' => $totalAmount,
            'originalTotalAmount' => $originalTotalAmount,
            'advanceAmount' => $paymentType === 'advance' ? $advanceAmount : $finalAmount,
            'paymentType' => $paymentType,
            'razorpayKeyId' => config('razorpay.key_id'),
            'paymentCoupons' => DiscountCoupon::availableForPayment('trademark_filing', Auth::id()),
            'autoApplyCoupon' => $autoApplyCoupon,
        ]);
    }

    /**
     * Create Razorpay order
     */
    public function createOrder(Request $request, $applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        // Get min and max from config
        $minAmount = config('razorpay.custom_payments.min_amount', 1);
        $maxAmount = config('razorpay.custom_payments.max_amount', 100000);

        $validated = $request->validate([
            'amount' => "required|numeric|min:$minAmount|max:$maxAmount",
            'payment_type' => 'required|in:advance,final,full,custom',
        ]);

        try {
            // Initialize Razorpay API using cURL (to avoid dependency)
            $razorpayKeyId = config('razorpay.key_id');
            $razorpaySecret = config('razorpay.key_secret');

            // Convert to paise (1 rupee = 100 paise)
            $amountInPaise = (int) ($validated['amount'] * 100);

            // Create Razorpay order via REST API
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_USERPWD, "$razorpayKeyId:$razorpaySecret");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => 'order_' . $application->id . '_' . time(),
                'description' => 'Trademark Registration - ' . $application->brand_name,
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception('Failed to create Razorpay order');
            }

            $order = json_decode($response, true);

            // Save payment record with pending status
            $normalizedPaymentType = match ($validated['payment_type']) {
                'final' => 'final',
                'full' => 'full',
                default => 'advance',
            };
            $paymentData = [
                'application_id' => $applicationId,
                'user_id' => Auth::id(),
                'amount' => $validated['amount'],
                'total_amount' => $application->entity_type === 'individual' ? 7000 : 9000,
                'percentage' => $normalizedPaymentType === 'advance' ? '50%' : '100%',
                'payment_method' => 'razorpay',
                'status' => 'pending',
                'reference_number' => $order['id'],
            ];

            if ($this->hasPaymentsColumn('payment_type')) {
                $paymentData['payment_type'] = $normalizedPaymentType;
            }

            $payment = Payment::create($paymentData);

            return response()->json([
                'status' => 'success',
                'order_id' => $order['id'],
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'key' => $razorpayKeyId,
                'user_email' => Auth::user()->email,
                'user_phone' => Auth::user()->phone ?? '',
                'user_name' => Auth::user()->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment signature
     */
    public function verifySignature(Request $request, $applicationId, TrademarkWorkflowService $workflow)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Verify signature manually
            $orderId = $validated['razorpay_order_id'];
            $paymentId = $validated['razorpay_payment_id'];
            $signature = $validated['razorpay_signature'];

            $secret = config('razorpay.key_secret');

            // Create the expected signature
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);

            if ($expectedSignature !== $signature) {
                throw new \Exception('Invalid payment signature');
            }

            // Update payment record
            $payment = Payment::where([
                'application_id' => $applicationId,
                'reference_number' => $orderId
            ])->firstOrFail();

            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'transaction_id' => $paymentId,
            ]);

            $paymentType = $this->paymentType($payment);

            if ($paymentType === 'final') {
                $workflow->markFinalPaymentComplete($application);
            } else {
                $workflow->markAdvancePaymentComplete($application);
            }

            return response()->json([
                'status' => 'success',
                'message' => $paymentType === 'final'
                    ? 'Final payment verified successfully.'
                    : 'Payment verified successfully. Your application has been sent for admin review.',
                'payment_id' => $payment->id,
                'redirect_url' => route('trademark.status', $application->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verify payment status (for status checking)
     */
    public function checkPaymentStatus($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        $payment = $application->payments()->where('status', 'completed')->latest('id')->first();

        return response()->json([
            'paid' => $payment ? true : false,
            'payment' => $payment,
        ]);
    }

    /**
     * Process payment (Legacy method - redirects to showPayment)
     */
    public function processPayment($applicationId)
    {
        $application = Application::findOrFail($applicationId);

        if ($application->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('payment.show', $application->id);
    }

    /**
     * Show payment history
     */
    public function paymentHistory()
    {
        $payments = Auth::user()->payments()->with('application')->latest()->get();
        return view('payments.history', ['payments' => $payments]);
    }

    public function viewInvoice(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array(strtolower((string) $payment->status), ['completed', 'approved'], true)) {
            abort(404);
        }

        $application = $payment->application;

        $pdf = \PDF::loadView('emails.attachments.invoice', [
            'application' => $application,
            'user' => $payment->user,
            'payment' => $payment,
            'invoiceNumber' => $this->invoiceNumber($payment),
            'paymentLabel' => $this->paymentLabel($payment),
            'issuedAt' => $payment->paid_at ?? $payment->created_at,
            'firmName' => config('app.name', 'Legal Bruz'),
            'firmEmail' => config('mail.from.address'),
        ])->setPaper('a4');

        return $pdf->stream('invoice-' . $application->id . '-' . $payment->id . '.pdf');
    }

    private function hasPaymentsColumn(string $column): bool
    {
        return Schema::hasColumn('payments', $column);
    }

    private function paymentType(Payment $payment): string
    {
        if ($this->hasPaymentsColumn('payment_type') && filled($payment->payment_type)) {
            return $payment->payment_type;
        }

        return (string) $payment->percentage === '100%' ? 'final' : 'advance';
    }

    private function paymentLabel(Payment $payment): string
    {
        return match ($this->paymentKind($payment)) {
            'full' => 'Full Payment',
            'final' => 'Final Payment',
            default => 'Advance Payment (50%)',
        };
    }

    private function paymentKind(Payment $payment): string
    {
        $paymentType = strtolower((string) ($payment->payment_type ?? ''));

        if (in_array($paymentType, ['advance', 'final', 'full'], true)) {
            return $paymentType;
        }

        if ((float) $payment->amount >= (float) $payment->total_amount && (float) $payment->total_amount > 0) {
            return 'full';
        }

        return (string) $payment->percentage === '100%' ? 'final' : 'advance';
    }

    private function invoiceNumber(Payment $payment): string
    {
        $issuedAt = $payment->paid_at ?? $payment->created_at ?? now();

        return 'INV-' . $issuedAt->format('Y') . '-' . $payment->application_id . '-' . $payment->id;
    }
}
