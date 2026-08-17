@extends('layouts.app-modern')

@section('content')
    @php
        $trademarkFormCoupon = auth()->check()
            ? \App\Models\DiscountCoupon::autoApplyForPayment('trademark_filing', auth()->id())
            : \App\Models\DiscountCoupon::autoApplyForPublicService('trademark_filing');
        $trademarkPricingPlans = $trademarkPricingPlans ?? \App\Models\TrademarkPricing::activePlans();
        $trademarkPricingDefaults = \App\Models\TrademarkPricing::defaults();
        $individualPlanAmount = (float) ($trademarkPricingPlans['individual']['amount'] ?? $trademarkPricingDefaults['individual']['amount']);
        $otherApplicantPlanAmount = (float) ($trademarkPricingPlans['company']['amount'] ?? $trademarkPricingDefaults['company']['amount']);
        $individualOfferAmount = $trademarkFormCoupon
            ? $trademarkFormCoupon->discountedAmountFor($individualPlanAmount)
            : $individualPlanAmount;
        $otherApplicantOfferAmount = $trademarkFormCoupon
            ? $trademarkFormCoupon->discountedAmountFor($otherApplicantPlanAmount)
            : $otherApplicantPlanAmount;
        $showTrademarkFormOffer = $trademarkFormCoupon
            && ($individualOfferAmount < $individualPlanAmount || $otherApplicantOfferAmount < $otherApplicantPlanAmount);
    @endphp

    <div class="container">
        <div class="progress-section">
            <div class="step-tracker step-tracker-5">
                <div class="step-item active" data-step-indicator="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Trademark Applicant Details</div>
                </div>
                <div class="step-item" data-step-indicator="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Details of Signatory</div>
                </div>
                <div class="step-item" data-step-indicator="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Co-Applicant / Partners</div>
                </div>
                <div class="step-item" data-step-indicator="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Trademark Details</div>
                </div>
                <div class="step-item" data-step-indicator="5">
                    <div class="step-number">5</div>
                    <div class="step-label">Billing Company Details</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h2>Trademark Filing in India</h2>
                        <small>Complete all 5 steps before moving to payment</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('trademark.store') }}" method="POST" enctype="multipart/form-data" id="multiStepTrademarkForm">
                            @csrf

                            <div class="section-block form-step" data-step="5">
                                <div class="section-title-row">
                                    <span class="section-step">E.</span>
                                    <h5>Billing Company Details</h5>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="billing_name" class="form-label required">Billing Name</label>
                                        <input type="text" id="billing_name" name="billing_name"
                                            class="form-control @error('billing_name') is-invalid @enderror"
                                            value="{{ old('billing_name', auth()->user()->name ?? '') }}" required>
                                        @error('billing_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_email" class="form-label required">Email ID</label>
                                        <input type="email" id="billing_email" name="billing_email"
                                            class="form-control @error('billing_email') is-invalid @enderror"
                                            value="{{ old('billing_email', auth()->user()->email ?? '') }}" required>
                                        @error('billing_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-8">
                                        <label for="billing_address" class="form-label required">Billing Address</label>
                                        <textarea id="billing_address" name="billing_address" rows="3"
                                            class="form-control @error('billing_address') is-invalid @enderror" required>{{ old('billing_address') }}</textarea>
                                        @error('billing_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="billing_mobile" class="form-label required">Mobile Number</label>
                                        <input type="text" id="billing_mobile" name="billing_mobile"
                                            class="form-control @error('billing_mobile') is-invalid @enderror"
                                            value="{{ old('billing_mobile') }}" inputmode="numeric" maxlength="10"
                                            pattern="[6789][0-9]{9}" title="Enter a 10-digit Indian mobile number"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)" required>
                                        @error('billing_mobile')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gst_number" class="form-label">GST Number</label>
                                        <input type="text" id="gst_number" name="gst_number"
                                            class="form-control @error('gst_number') is-invalid @enderror"
                                            value="{{ old('gst_number') }}" maxlength="15"
                                            pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}"
                                            title="Enter a valid GST number" oninput="this.value = this.value.toUpperCase()">
                                        @error('gst_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-lg prev-step-btn" data-prev-step="4">
                                        <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back to Step 4
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Continue to Payment<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="section-block form-step active" data-step="1">
                                <div class="section-title-row">
                                    <span class="section-step">A.</span>
                                    <h5>Trademark Applicant Details (Owner of the Trademark)</h5>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="applicant_name" class="form-label required">Applicant Name</label>
                                        <input type="text" id="applicant_name" name="applicant_name"
                                            class="form-control @error('applicant_name') is-invalid @enderror"
                                            value="{{ old('applicant_name', auth()->user()->name ?? '') }}" required>
                                        @error('applicant_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="applicant_email" class="form-label required">Email ID</label>
                                        <input type="email" id="applicant_email" name="applicant_email"
                                            class="form-control @error('applicant_email') is-invalid @enderror"
                                            value="{{ old('applicant_email', auth()->user()->email ?? '') }}" required>
                                        @error('applicant_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="applicant_address" class="form-label required">Address of Applicant</label>
                                        <textarea id="applicant_address" name="applicant_address" rows="3"
                                            class="form-control @error('applicant_address') is-invalid @enderror" required>{{ old('applicant_address') }}</textarea>
                                        @error('applicant_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant_pincode" class="form-label required">Pin Code</label>
                                        <input type="text" id="applicant_pincode" name="applicant_pincode"
                                            class="form-control @error('applicant_pincode') is-invalid @enderror"
                                            value="{{ old('applicant_pincode') }}" inputmode="numeric" maxlength="6"
                                            pattern="[0-9]{6}" title="Enter a 6-digit pincode"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)" required>
                                        @error('applicant_pincode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant_state" class="form-label required">State</label>
                                        <select id="applicant_state" name="applicant_state"
                                            class="form-select @error('applicant_state') is-invalid @enderror"
                                            data-selected="{{ old('applicant_state') }}" required>
                                            <option value="{{ old('applicant_state') }}">{{ old('applicant_state') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('applicant_state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="applicant_district" class="form-label required">District</label>
                                        <select id="applicant_district" name="applicant_district"
                                            class="form-select @error('applicant_district') is-invalid @enderror"
                                            data-selected="{{ old('applicant_district') }}" required>
                                            <option value="{{ old('applicant_district') }}">{{ old('applicant_district') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('applicant_district')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="applicant_phone" class="form-label required">Mobile Number</label>
                                        <input type="text" id="applicant_phone" name="applicant_phone"
                                            class="form-control @error('applicant_phone') is-invalid @enderror"
                                            value="{{ old('applicant_phone') }}" inputmode="numeric" maxlength="10"
                                            pattern="[6789][0-9]{9}" title="Enter a 10-digit Indian mobile number"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)" required>
                                        @error('applicant_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="type_of_applicant" class="form-label required">Type of Applicant</label>
                                        <select id="type_of_applicant" name="type_of_applicant"
                                            class="form-select @error('type_of_applicant') is-invalid @enderror" required>
                                            <option value="">Select applicant type</option>
                                            <option value="individual"
                                                {{ old('type_of_applicant') === 'individual' ? 'selected' : '' }}>
                                                Individual / Proprietor / Trader</option>
                                            <option value="company"
                                                {{ old('type_of_applicant') === 'company' ? 'selected' : '' }}>
                                                Company</option>
                                            <option value="llp"
                                                {{ old('type_of_applicant') === 'llp' ? 'selected' : '' }}>LLP
                                            </option>
                                            <option value="ngo"
                                                {{ old('type_of_applicant') === 'ngo' ? 'selected' : '' }}>NGO
                                            </option>
                                            <option value="small_enterprise"
                                                {{ old('type_of_applicant') === 'small_enterprise' ? 'selected' : '' }}>
                                                Small Enterprise</option>
                                            <option value="startup"
                                                {{ old('type_of_applicant') === 'startup' ? 'selected' : '' }}>Startup
                                            </option>
                                        </select>
                                        <div class="alert alert-warning mt-3 mb-0 py-2 px-3 small fw-semibold">
                                            Fees vary based on applicant type. Please select the appropriate category for your trademark application.
                                        </div>
                                        @error('type_of_applicant')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back
                                    </a>
                                    <button type="button" class="btn btn-primary btn-lg next-step-btn" data-next-step="2">
                                        Continue to Step 2<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="section-block form-step" data-step="2">
                                <div class="section-title-row">
                                    <span class="section-step">B.</span>
                                    <h5>Details of Signatory (Director, Partner, Authorised Signatory) (if any)</h5>
                                </div>
                                <div class="signatory-owner-note d-none" data-signatory-owner-note>
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span><strong>Individual / Proprietor / Trader:</strong> The signatory person is the owner of the business.</span>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="signatory_name" class="form-label required">Name of Signatory</label>
                                        <input type="text" id="signatory_name" name="signatory_name"
                                            class="form-control @error('signatory_name') is-invalid @enderror"
                                            value="{{ old('signatory_name') }}" required>
                                        @error('signatory_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="signatory_father_name" class="form-label required">Father's Name</label>
                                        <input type="text" id="signatory_father_name" name="signatory_father_name"
                                            class="form-control @error('signatory_father_name') is-invalid @enderror"
                                            value="{{ old('signatory_father_name') }}" required>
                                        @error('signatory_father_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="signatory_address" class="form-label required">Address of
                                            AR/Signatory</label>
                                        <textarea id="signatory_address" name="signatory_address" rows="3"
                                            class="form-control @error('signatory_address') is-invalid @enderror" required>{{ old('signatory_address') }}</textarea>
                                        @error('signatory_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="signatory_pincode" class="form-label required">Pin Code</label>
                                        <input type="text" id="signatory_pincode" name="signatory_pincode"
                                            class="form-control @error('signatory_pincode') is-invalid @enderror"
                                            value="{{ old('signatory_pincode') }}" inputmode="numeric" maxlength="6"
                                            pattern="[0-9]{6}" title="Enter a 6-digit pincode"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)" required>
                                        @error('signatory_pincode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="signatory_state" class="form-label required">State</label>
                                        <select id="signatory_state" name="signatory_state"
                                            class="form-select @error('signatory_state') is-invalid @enderror"
                                            data-selected="{{ old('signatory_state') }}" required>
                                            <option value="{{ old('signatory_state') }}">{{ old('signatory_state') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('signatory_state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="signatory_district" class="form-label required">District</label>
                                        <select id="signatory_district" name="signatory_district"
                                            class="form-select @error('signatory_district') is-invalid @enderror"
                                            data-selected="{{ old('signatory_district') }}" required>
                                            <option value="{{ old('signatory_district') }}">{{ old('signatory_district') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('signatory_district')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="signatory_phone" class="form-label">Mobile Number</label>
                                        <input type="text" id="signatory_phone" name="signatory_phone"
                                            class="form-control @error('signatory_phone') is-invalid @enderror"
                                            value="{{ old('signatory_phone') }}" inputmode="numeric" maxlength="10"
                                            pattern="[6789][0-9]{9}" title="Enter a 10-digit Indian mobile number"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                                        @error('signatory_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="signatory_email" class="form-label">Email ID</label>
                                        <input type="email" id="signatory_email" name="signatory_email"
                                            class="form-control @error('signatory_email') is-invalid @enderror"
                                            value="{{ old('signatory_email') }}">
                                        @error('signatory_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="signatory_designation" class="form-label required">Designation of
                                            Signatory</label>
                                        <select id="signatory_designation" name="signatory_designation"
                                            class="form-select @error('signatory_designation') is-invalid @enderror"
                                            required>
                                            <option value="">Select designation</option>
                                            <option value="director"
                                                {{ old('signatory_designation') === 'director' ? 'selected' : '' }}>
                                                Director</option>
                                            <option value="partner"
                                                {{ old('signatory_designation') === 'partner' ? 'selected' : '' }}>Partner
                                            </option>
                                            <option value="proprietor"
                                                {{ old('signatory_designation') === 'proprietor' ? 'selected' : '' }}>
                                                Proprietor / Individual / Trader</option>
                                            <option value="authorised_signatory"
                                                {{ old('signatory_designation') === 'authorised_signatory' ? 'selected' : '' }}>
                                                Authorised Signatory</option>
                                        </select>
                                        @error('signatory_designation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-lg prev-step-btn" data-prev-step="1">
                                        <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back to Step 1
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg next-step-btn" data-next-step="3">
                                        Continue to Step 3<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="section-block form-step" data-step="3">
                                <div class="section-title-row">
                                    <span class="section-step">C.</span>
                                    <h5>Details of Co-Applicant/Joint Applicant or Partners</h5>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="co_applicant_name" class="form-label">Name of Co-applicant / Partner</label>
                                        <input type="text" id="co_applicant_name" name="co_applicant_name"
                                            class="form-control @error('co_applicant_name') is-invalid @enderror"
                                            value="{{ old('co_applicant_name') }}">
                                        @error('co_applicant_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="co_applicant_father_name" class="form-label">Father's Name</label>
                                        <input type="text" id="co_applicant_father_name" name="co_applicant_father_name"
                                            class="form-control @error('co_applicant_father_name') is-invalid @enderror"
                                            value="{{ old('co_applicant_father_name') }}">
                                        @error('co_applicant_father_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="co_applicant_address" class="form-label">Address</label>
                                        <textarea id="co_applicant_address" name="co_applicant_address" rows="3"
                                            class="form-control @error('co_applicant_address') is-invalid @enderror">{{ old('co_applicant_address') }}</textarea>
                                        @error('co_applicant_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="co_applicant_pincode" class="form-label">Pin Code</label>
                                        <input type="text" id="co_applicant_pincode" name="co_applicant_pincode"
                                            class="form-control @error('co_applicant_pincode') is-invalid @enderror"
                                            value="{{ old('co_applicant_pincode') }}" inputmode="numeric" maxlength="6"
                                            pattern="[0-9]{6}" title="Enter a 6-digit pincode"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)">
                                        @error('co_applicant_pincode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="co_applicant_state" class="form-label">State</label>
                                        <select id="co_applicant_state" name="co_applicant_state"
                                            class="form-select @error('co_applicant_state') is-invalid @enderror"
                                            data-selected="{{ old('co_applicant_state') }}">
                                            <option value="{{ old('co_applicant_state') }}">{{ old('co_applicant_state') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('co_applicant_state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="co_applicant_district" class="form-label">District</label>
                                        <select id="co_applicant_district" name="co_applicant_district"
                                            class="form-select @error('co_applicant_district') is-invalid @enderror"
                                            data-selected="{{ old('co_applicant_district') }}">
                                            <option value="{{ old('co_applicant_district') }}">{{ old('co_applicant_district') ?: 'Enter pincode first' }}</option>
                                        </select>
                                        @error('co_applicant_district')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="co_applicant_mobile" class="form-label">Mobile Number</label>
                                        <input type="text" id="co_applicant_mobile" name="co_applicant_mobile"
                                            class="form-control @error('co_applicant_mobile') is-invalid @enderror"
                                            value="{{ old('co_applicant_mobile') }}" inputmode="numeric" maxlength="10"
                                            pattern="[6789][0-9]{9}" title="Enter a 10-digit Indian mobile number"
                                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                                        @error('co_applicant_mobile')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="co_applicant_email" class="form-label">Email ID</label>
                                        <input type="email" id="co_applicant_email" name="co_applicant_email"
                                            class="form-control @error('co_applicant_email') is-invalid @enderror"
                                            value="{{ old('co_applicant_email') }}">
                                        @error('co_applicant_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="co_applicant_designation" class="form-label">Designation</label>
                                        <select id="co_applicant_designation" name="co_applicant_designation"
                                            class="form-select @error('co_applicant_designation') is-invalid @enderror">
                                            <option value="">Select designation</option>
                                            <option value="co_applicant"
                                                {{ old('co_applicant_designation') === 'co_applicant' ? 'selected' : '' }}>
                                                Co-applicant</option>
                                            <option value="partner"
                                                {{ old('co_applicant_designation') === 'partner' ? 'selected' : '' }}>
                                                Partner</option>
                                        </select>
                                        @error('co_applicant_designation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-lg prev-step-btn" data-prev-step="2">
                                        <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back to Step 2
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg next-step-btn" data-next-step="4">
                                        Continue to Step 4<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="section-block form-step" data-step="4">
                                <div class="section-title-row">
                                    <span class="section-step">D.</span>
                                    <h5>Trademark Details</h5>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="trademark_type" class="form-label required">Select the Type of Trademark</label>
                                        <select id="trademark_type" name="trademark_type"
                                            class="form-select @error('trademark_type') is-invalid @enderror" required>
                                            <option value="">Select trademark type</option>
                                            <option value="word" {{ old('trademark_type') === 'word' ? 'selected' : '' }}>Word</option>
                                            <option value="device" {{ old('trademark_type') === 'device' ? 'selected' : '' }}>Device</option>
                                            <option value="shape_of_goods" {{ old('trademark_type') === 'shape_of_goods' ? 'selected' : '' }}>Shape of Goods</option>
                                            <option value="colour" {{ old('trademark_type') === 'colour' ? 'selected' : '' }}>Colour</option>
                                            <option value="sound_mark" {{ old('trademark_type') === 'sound_mark' ? 'selected' : '' }}>Sound Mark</option>
                                            <option value="three_dimensional" {{ old('trademark_type') === 'three_dimensional' ? 'selected' : '' }}>Three Dimensional</option>
                                            <option value="taste_mark" {{ old('trademark_type') === 'taste_mark' ? 'selected' : '' }}>Taste Mark</option>
                                            <option value="smell_mark" {{ old('trademark_type') === 'smell_mark' ? 'selected' : '' }}>Smell Mark</option>
                                        </select>
                                        @error('trademark_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mark_brand" class="form-label required">Mark/Brand (in words)</label>
                                        <input type="text" id="mark_brand" name="mark_brand"
                                            class="form-control @error('mark_brand') is-invalid @enderror"
                                            value="{{ old('mark_brand') }}" required>
                                        @error('mark_brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="trademark_language" class="form-label required">Language of Trademark</label>
                                        <select id="trademark_language" name="trademark_language"
                                            class="form-select @error('trademark_language') is-invalid @enderror" required>
                                            @include('trademark.partials.language-options', [
                                                'selectedLanguage' => old('trademark_language'),
                                            ])
                                        </select>
                                        @error('trademark_language')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="trademark_origin_description" class="form-label">A Brief Description of the Origin of the Trademark</label>
                                        <textarea id="trademark_origin_description" name="trademark_origin_description" rows="3"
                                            class="form-control @error('trademark_origin_description') is-invalid @enderror">{{ old('trademark_origin_description') }}</textarea>
                                        @error('trademark_origin_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="mark_conditions" class="form-label">Conditions or Limitations to the Mark (if any)</label>
                                        <textarea id="mark_conditions" name="mark_conditions" rows="2"
                                            class="form-control @error('mark_conditions') is-invalid @enderror">{{ old('mark_conditions') }}</textarea>
                                        @error('mark_conditions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="trademark_image" class="form-label required">Image of the Trademark</label>
                                        <input type="file" id="trademark_image" name="trademark_image"
                                            class="form-control @error('trademark_image') is-invalid @enderror"
                                            accept="image/png,image/jpeg,image/jpg,image/webp" required>
                                        <small class="text-muted d-block mt-2">Allowed formats: JPG, PNG, WEBP. Max size: 4 MB.</small>
                                        @error('trademark_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="goods_services" class="form-label required">Goods or Services for which the TM is Used or Proposed to be Used</label>
                                        <textarea id="goods_services" name="goods_services" rows="3"
                                            class="form-control @error('goods_services') is-invalid @enderror" required>{{ old('goods_services') }}</textarea>
                                        @error('goods_services')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="trade_description" class="form-label required">Trade Description</label>
                                        <select id="trade_description" name="trade_description"
                                            class="form-select @error('trade_description') is-invalid @enderror" required>
                                            <option value="">Select trade description</option>
                                            <option value="manufacturer" {{ old('trade_description') === 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                            <option value="trader" {{ old('trade_description') === 'trader' ? 'selected' : '' }}>Trader</option>
                                            <option value="service_provider" {{ old('trade_description') === 'service_provider' ? 'selected' : '' }}>Service Provider</option>
                                        </select>
                                        @error('trade_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="trademark_usage_status" class="form-label required">Use Date of Trademark</label>
                                        <select id="trademark_usage_status" name="trademark_usage_status"
                                            class="form-select @error('trademark_usage_status') is-invalid @enderror" required>
                                            <option value="">Select usage status</option>
                                            <option value="used" {{ old('trademark_usage_status') === 'used' ? 'selected' : '' }}>Used</option>
                                            <option value="proposed" {{ old('trademark_usage_status') === 'proposed' ? 'selected' : '' }}>Proposed to be used</option>
                                        </select>
                                        @error('trademark_usage_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="trademark_use_date" class="form-label">Use Date of Trademark</label>
                                        <input type="date" id="trademark_use_date" name="trademark_use_date"
                                            class="form-control @error('trademark_use_date') is-invalid @enderror"
                                            value="{{ old('trademark_use_date') }}">
                                        @error('trademark_use_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="proof_of_use" class="form-label">Proof of Use of Trademark</label>
                                        <input type="file" id="proof_of_use" name="proof_of_use"
                                            class="form-control @error('proof_of_use') is-invalid @enderror"
                                            accept=".pdf,image/png,image/jpeg,image/jpg,image/webp">
                                        <small class="text-muted d-block mt-2">Allowed formats: PDF, JPG, PNG, WEBP. Max size: 5 MB.</small>
                                        @error('proof_of_use')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="application_type" class="form-label required">Application Type</label>
                                        <select id="application_type" name="application_type"
                                            class="form-select @error('application_type') is-invalid @enderror" required>
                                            <option value="">Select application type</option>
                                            <option value="trademark" {{ old('application_type') === 'trademark' ? 'selected' : '' }}>Trademark</option>
                                            <option value="certification" {{ old('application_type') === 'certification' ? 'selected' : '' }}>Certification</option>
                                            <option value="collective" {{ old('application_type') === 'collective' ? 'selected' : '' }}>Collective</option>
                                            <option value="series" {{ old('application_type') === 'series' ? 'selected' : '' }}>Series</option>
                                        </select>
                                        @error('application_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="alert alert-info mt-4 mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Next Step:</strong> After submitting this form, you'll need to complete billing details and then proceed to payment.
                                    @if ($showTrademarkFormOffer)
                                        With offer {{ $trademarkFormCoupon->code }}, individual applicants pay from the
                                        <span class="plan-price-old">₹{{ number_format($individualPlanAmount, 0) }}</span>
                                        <strong class="plan-price-offer">₹{{ number_format($individualOfferAmount, 0) }}</strong>
                                        plan and all other applicant types pay from the
                                        <span class="plan-price-old">₹{{ number_format($otherApplicantPlanAmount, 0) }}</span>
                                        <strong class="plan-price-offer">₹{{ number_format($otherApplicantOfferAmount, 0) }}</strong>
                                        plan.
                                    @else
                                        Individual applicants pay from the ₹{{ number_format($individualPlanAmount, 0) }} plan and all other
                                        applicant types pay from the ₹{{ number_format($otherApplicantPlanAmount, 0) }} plan.
                                    @endif
                                </div>

                                <div class="step-actions">
                                    <button type="button" class="btn btn-outline-secondary btn-lg prev-step-btn" data-prev-step="3">
                                        <i class="bi bi-arrow-left" style="margin-right: 8px;"></i>Back to Step 3
                                    </button>
                                    <button type="button" class="btn btn-primary btn-lg next-step-btn" data-next-step="5">
                                        Continue to Step 5<i class="bi bi-arrow-right" style="margin-left: 8px;"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .section-block + .section-block {
            margin-top: 36px;
        }

        .plan-price-old {
            color: #6c757d;
            font-weight: 600;
            margin: 0 3px;
            text-decoration: line-through;
            text-decoration-thickness: 2px;
        }

        .plan-price-offer {
            color: #5546ea;
            margin: 0 3px;
            white-space: nowrap;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .section-title-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--border);
        }

        .section-step {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--navy);
            line-height: 1;
        }

        .section-title-row h5 {
            margin: 0;
            color: var(--navy);
            font-weight: 800;
        }

        .signatory-owner-note:not(.d-none) {
            display: flex;
        }

        .signatory-owner-note {
            align-items: flex-start;
            gap: 10px;
            margin: -8px 0 22px;
            padding: 14px 16px;
            border: 1px solid rgba(42, 157, 143, 0.28);
            border-left: 4px solid var(--teal);
            border-radius: 10px;
            background: rgba(42, 157, 143, 0.08);
            color: #173f3a;
            font-weight: 700;
        }

        .signatory-owner-note i {
            color: var(--teal);
            margin-top: 2px;
        }

        .step-actions {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 30px;
        }

        .step-actions .btn {
            min-width: 220px;
        }

        @media (max-width: 768px) {
            .section-title-row {
                align-items: flex-start;
            }

            .section-step {
                font-size: 1.5rem;
            }

            .step-actions {
                flex-direction: column;
            }

            .step-actions .btn {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('multiStepTrademarkForm');
            const steps = Array.from(document.querySelectorAll('.form-step'));
            const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
            const trademarkImageInput = document.getElementById('trademark_image');
            const proofOfUseInput = document.getElementById('proof_of_use');
            const applicantTypeSelect = document.getElementById('type_of_applicant');
            const signatoryOwnerNote = document.querySelector('[data-signatory-owner-note]');
            let currentStep = 1;
            const MB = 1024 * 1024;
            const fileRules = [
                { input: trademarkImageInput, maxBytes: 4 * MB, label: 'Trademark image' },
                { input: proofOfUseInput, maxBytes: 5 * MB, label: 'Proof of use' },
            ];
            const pincodeLookups = ['applicant', 'signatory', 'co_applicant'];

            function uniqueValues(values) {
                return Array.from(new Set(values.filter(Boolean))).sort();
            }

            function setSelectOptions(select, values, selectedValue, placeholder) {
                const selected = selectedValue && values.includes(selectedValue) ? selectedValue : values[0] || '';
                select.innerHTML = '';

                if (!values.length) {
                    select.append(new Option(placeholder, ''));
                    return;
                }

                values.forEach((value) => {
                    select.append(new Option(value, value, false, value === selected));
                });
            }

            function resetLocationFields(stateSelect, districtSelect, message = 'Enter pincode first') {
                setSelectOptions(stateSelect, [], '', message);
                setSelectOptions(districtSelect, [], '', message);
            }

            function setupPincodeLookup(prefix) {
                const pincodeInput = document.getElementById(`${prefix}_pincode`);
                const stateSelect = document.getElementById(`${prefix}_state`);
                const districtSelect = document.getElementById(`${prefix}_district`);
                let latestRequest = 0;

                if (!pincodeInput || !stateSelect || !districtSelect) {
                    return;
                }

                async function loadLocation() {
                    const pincode = pincodeInput.value.replace(/\D/g, '').slice(0, 6);
                    pincodeInput.value = pincode;

                    if (pincode.length !== 6) {
                        resetLocationFields(stateSelect, districtSelect);
                        return;
                    }

                    const requestId = ++latestRequest;
                    resetLocationFields(stateSelect, districtSelect, 'Loading...');

                    try {
                        const response = await fetch(`{{ url('/api/indian-postal-codes') }}/${pincode}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const result = await response.json();

                        if (requestId !== latestRequest) {
                            return;
                        }

                        const states = result?.status === 'success' ? uniqueValues(result.states || []) : [];
                        const districts = result?.status === 'success' ? uniqueValues(result.districts || []) : [];

                        if (!states.length || !districts.length) {
                            resetLocationFields(stateSelect, districtSelect, 'No location found');
                            return;
                        }

                        setSelectOptions(stateSelect, states, stateSelect.dataset.selected, 'Select state');
                        setSelectOptions(districtSelect, districts, districtSelect.dataset.selected, 'Select district');
                        stateSelect.dataset.selected = stateSelect.value;
                        districtSelect.dataset.selected = districtSelect.value;
                    } catch (error) {
                        resetLocationFields(stateSelect, districtSelect, 'Unable to fetch location');
                    }
                }

                pincodeInput.addEventListener('input', loadLocation);
                pincodeInput.addEventListener('blur', loadLocation);

                if (pincodeInput.value.replace(/\D/g, '').length === 6) {
                    loadLocation();
                }
            }

            function updateStepper(step) {
                indicators.forEach((indicator, index) => {
                    const indicatorStep = index + 1;
                    indicator.classList.remove('active', 'completed');

                    if (indicatorStep < step) {
                        indicator.classList.add('completed');
                    } else if (indicatorStep === step) {
                        indicator.classList.add('active');
                    }
                });
            }

            function showStep(step) {
                currentStep = step;
                steps.forEach((section) => {
                    section.classList.toggle('active', Number(section.dataset.step) === step);
                });
                updateStepper(step);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function validateCurrentStep() {
                const activeStep = form.querySelector(`.form-step[data-step="${currentStep}"]`);
                if (!activeStep) {
                    return true;
                }

                const fields = Array.from(activeStep.querySelectorAll('input, select, textarea'));

                for (const field of fields) {
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        return false;
                    }
                }

                return true;
            }

            function validateFileSizes() {
                for (const rule of fileRules) {
                    const file = rule.input?.files?.[0];
                    if (file && file.size > rule.maxBytes) {
                        alert(`${rule.label} must be ${Math.round(rule.maxBytes / MB)} MB or smaller.`);
                        rule.input.value = '';
                        rule.input.focus();
                        return false;
                    }
                }

                return true;
            }

            document.querySelectorAll('.next-step-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    if (!validateCurrentStep()) {
                        return;
                    }

                    showStep(Number(this.dataset.nextStep));
                });
            });

            document.querySelectorAll('.prev-step-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    showStep(Number(this.dataset.prevStep));
                });
            });

            form.addEventListener('submit', function(event) {
                if (!validateCurrentStep() || !validateFileSizes()) {
                    event.preventDefault();
                }
            });

            function updateSignatoryOwnerNote() {
                if (!applicantTypeSelect || !signatoryOwnerNote) {
                    return;
                }

                signatoryOwnerNote.classList.toggle('d-none', applicantTypeSelect.value !== 'individual');
            }

            applicantTypeSelect?.addEventListener('change', updateSignatoryOwnerNote);
            updateSignatoryOwnerNote();

            pincodeLookups.forEach(setupPincodeLookup);
            showStep(1);
        });
    </script>
@endsection
