@if (isset($paymentCoupons) && $paymentCoupons->isNotEmpty())
    @php
        $couponInputName = $couponInputName ?? 'discount_coupon_id';
        $selectedCouponId = old($couponInputName, $selectedCouponId ?? ($paymentCoupons->count() === 1 ? $paymentCoupons->first()->id : null));
        $paymentAmount = isset($paymentAmount) ? (float) $paymentAmount : null;
    @endphp
    <style>
        .payment-coupons-card {
            border: 1px solid #dbe7f2 !important;
            border-radius: 10px;
            background: #f8fafc;
            box-shadow: none !important;
        }

        .payment-coupons-card .card-body {
            padding: 14px !important;
        }

        .payment-coupons-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 10px;
            color: #1d3557;
            font-size: 0.88rem;
            font-weight: 950;
            line-height: 1.25;
        }

        .payment-coupon-list {
            display: grid;
            gap: 8px;
        }

        .payment-coupon-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            padding: 11px;
            border: 1px solid #cbdaf0;
            border-radius: 9px;
            background: #ffffff;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .payment-coupon-item:has(.payment-coupon-radio:checked) {
            border-color: #155eef;
            box-shadow: 0 0 0 3px rgba(21, 94, 239, 0.1);
        }

        .payment-coupon-item:hover {
            border-color: #7ba7f8;
        }

        .payment-coupon-radio {
            width: 16px;
            height: 16px;
            accent-color: #155eef;
        }

        .payment-coupon-code {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            padding: 0.26rem 0.52rem;
            border-radius: 7px;
            background: #155eef;
            color: #ffffff;
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.04em;
            overflow-wrap: anywhere;
        }

        .payment-coupon-description {
            margin: 6px 0 0;
            color: #475569;
            font-size: 0.76rem;
            font-weight: 750;
            line-height: 1.32;
        }

        .payment-coupon-discount {
            display: inline-flex;
            align-items: center;
            justify-self: start;
            margin-top: 7px;
            padding: 3px 7px;
            border-radius: 999px;
            color: #047857;
            background: #dcfce7;
            font-size: 0.72rem;
            font-weight: 950;
            white-space: nowrap;
        }
    </style>

    <div class="card mb-3 payment-coupons-card">
        <div class="card-body">
            <h5 class="payment-coupons-title">
                <i class="fas fa-tags"></i> Available Discount Coupons
            </h5>
            <div class="payment-coupon-list">
                @foreach ($paymentCoupons as $coupon)
                    @php
                        $discountAmount = $paymentAmount !== null ? $coupon->discountAmountFor($paymentAmount) : null;
                        $discountedAmount = $paymentAmount !== null ? $coupon->discountedAmountFor($paymentAmount) : null;
                    @endphp
                    <label class="payment-coupon-item" for="payment-coupon-{{ $couponInputName }}-{{ $coupon->id }}">
                        <input
                            type="radio"
                            class="payment-coupon-radio"
                            id="payment-coupon-{{ $couponInputName }}-{{ $coupon->id }}"
                            name="{{ $couponInputName }}"
                            value="{{ $coupon->id }}"
                            @if ($discountAmount !== null) data-discount-amount="{{ number_format($discountAmount, 2, '.', '') }}" @endif
                            @if ($discountedAmount !== null) data-payable-amount="{{ number_format($discountedAmount, 2, '.', '') }}" @endif
                            @checked((string) $selectedCouponId === (string) $coupon->id)
                        >
                        <div>
                            <span class="payment-coupon-code">{{ $coupon->code }}</span>
                            <p class="payment-coupon-description">
                                {{ $coupon->description ?: $coupon->title }}
                            </p>
                            <div class="payment-coupon-discount">{{ $coupon->discount_label }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
@endif
