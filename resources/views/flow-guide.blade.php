<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Trademark Registration Flow | Legal Bruz (LLP)</title>
    <link rel="icon" type="image/png" href="{{ asset('logo4.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/RegistrationGuide.css') }}?v={{ filemtime(public_path('css/RegistrationGuide.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">


    <style>
        /* ============ FLOW DESIGN SECTION ============ */
        .flow-section {
            position: relative;
            overflow: hidden;
            padding: 92px 0 76px;
            background:
                radial-gradient(circle at 0 0, rgba(42, 157, 143, 0.14) 0 92px, transparent 93px),
                radial-gradient(circle at 100% 0, rgba(42, 157, 143, 0.12) 0 92px, transparent 93px),
                linear-gradient(180deg, #ffffff 0%, #fbfefe 100%);
        }

        .flow-section::before,
        .flow-section::after {
            content: "";
            position: absolute;
            width: 118px;
            height: 90px;
            opacity: 0.48;
            background-image: radial-gradient(circle, rgba(42, 157, 143, 0.38) 2px, transparent 3px);
            background-size: 22px 22px;
            pointer-events: none;
        }

        .flow-section::before {
            top: 24px;
            left: 26px;
        }

        .flow-section::after {
            top: 62px;
            right: 88px;
        }

        .flow-section .container {
            position: relative;
            z-index: 1;
        }

        .flow-header {
            max-width: 960px;
            margin: 0 auto 76px;
            text-align: center;
        }

        .flow-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 22px;
            margin-bottom: 22px;
            border-radius: 999px;
            background: #eaf8f6;
            color: #079987;
            font-size: 0.9rem;
            font-weight: 900;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .flow-header h2 {
            margin-bottom: 18px;
            color: var(--navy);
            font-size: 2.8rem;
            line-height: 1.02;
            letter-spacing: 0;
        }

        .flow-header h2 span {
            color: #09a895;
        }

        .flow-header p {
            margin: 0;
            color: #555a62;
            font-size: 1.12rem;
            font-weight: 500;
        }

        .flow-timeline {
            position: relative;
            display: grid;
            grid-template-columns: repeat(8, minmax(104px, 1fr));
            gap: 16px;
            margin-bottom: 52px;
        }

        .flow-timeline::before {
            content: "";
            position: absolute;
            top: 31px;
            left: 4%;
            right: 4%;
            height: 4px;
            background: linear-gradient(90deg, #0a9f91 0%, rgba(10, 159, 145, 0.18) 100%);
        }

        .flow-step {
            position: relative;
            min-width: 0;
            padding: 0;
            text-align: center;
        }

        .flow-step::before {
            content: "";
            position: absolute;
            top: 28px;
            right: -13px;
            z-index: 2;
            width: 12px;
            height: 12px;
            border: 2px solid rgba(10, 159, 145, 0.3);
            border-radius: 50%;
            background: #eefaf8;
        }

        .flow-step:last-child::before {
            display: none;
        }

        .step-circle {
            position: relative;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            margin-bottom: 64px;
            border: 0;
            border-radius: 50%;
            color: #ffffff;
            background:
                radial-gradient(circle at 32% 22%, rgba(255, 255, 255, 0.32), transparent 28px),
                linear-gradient(135deg, #14b8a6 0%, #078d80 100%);
            box-shadow: 0 13px 25px rgba(8, 141, 128, 0.28);
            font-size: 1.45rem;
            font-weight: 900;
            line-height: 1;
        }

        .step-circle::before,
        .step-circle::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .step-circle::before {
            bottom: -48px;
            width: 2px;
            height: 46px;
            background: rgba(10, 159, 145, 0.25);
        }

        .step-circle::after {
            bottom: -52px;
            width: 9px;
            height: 9px;
            border: 1px solid rgba(10, 159, 145, 0.26);
            border-radius: 50%;
            background: #d9f2ef;
        }

        .flow-card {
            position: relative;
            min-height: 300px;
            padding: 32px 14px 26px;
            border: 1px solid #e5eeee;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 18px 36px rgba(29, 53, 87, 0.09);
        }

        .flow-card::before {
            content: "";
            position: absolute;
            top: -21px;
            left: 50%;
            width: 42px;
            height: 42px;
            transform: translateX(-50%) rotate(45deg);
            border-top: 1px solid #e5eeee;
            border-left: 1px solid #e5eeee;
            border-radius: 6px 0 0 0;
            background: #ffffff;
        }

        .flow-icon {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 84px;
            height: 84px;
            margin-bottom: 24px;
            border-radius: 50%;
            background: #edf7f6;
            color: #079987;
            font-size: 2rem;
        }

        .flow-section .step-title {
            display: block;
            min-height: 40px;
            margin-bottom: 22px;
            color: var(--navy);
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.25;
        }

        .flow-section .step-title::before {
            content: none;
        }

        .flow-section .step-description {
            min-height: 70px;
            max-width: 132px;
            margin: 0 auto;
            color: #4f545c;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .flow-action {
            position: relative;
            overflow: hidden;
            display: grid;
            place-items: center;
            min-height: 230px;
            padding: 42px 22px;
            border: 1px solid #dcefed;
            border-radius: 20px;
            background:
                radial-gradient(circle at 95% 110%, rgba(42, 157, 143, 0.15) 0 126px, transparent 127px),
                linear-gradient(135deg, #f9fefd 0%, #f4fbfa 100%);
            text-align: center;
        }

        .flow-action::before {
            content: "";
            position: absolute;
            left: 76px;
            bottom: 34px;
            width: 240px;
            height: 130px;
            background:
                linear-gradient(150deg, transparent 0 41%, #079987 42% 58%, transparent 59%),
                linear-gradient(35deg, transparent 0 40%, #0aa896 41% 58%, transparent 59%);
            clip-path: polygon(0 16%, 100% 0, 68% 76%, 47% 58%, 26% 86%);
            opacity: 0.9;
            transform: rotate(-9deg) scale(0.36);
            transform-origin: left bottom;
        }

        .flow-action::after {
            content: "";
            position: absolute;
            right: 70px;
            top: 42px;
            width: 260px;
            height: 82px;
            opacity: 0.45;
            border-top: 2px dashed rgba(10, 159, 145, 0.35);
            border-radius: 50%;
            transform: rotate(-16deg);
        }

        .flow-action-content {
            position: relative;
            z-index: 1;
            max-width: 560px;
        }

        .flow-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 14px;
            border-radius: 50%;
            background: #e4f6f3;
            color: #079987;
            font-size: 1.7rem;
        }

        .flow-action h3 {
            margin-bottom: 6px;
            color: #079987;
            font-size: 1.28rem;
            font-weight: 900;
        }

        .flow-action p {
            margin-bottom: 26px;
            color: #4f545c;
            font-size: 1.02rem;
        }

        .flow-guide-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-width: min(100%, 340px);
            padding: 15px 30px;
            border-radius: 9px;
            background: linear-gradient(135deg, #10b7a5 0%, #079987 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 900;
            text-decoration: none;
            box-shadow: 0 16px 34px rgba(7, 153, 135, 0.28);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .flow-guide-btn:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(7, 153, 135, 0.34);
        }

        @media (max-width: 1199px) {
            .flow-timeline {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 44px 24px;
                max-width: 900px;
                margin-inline: auto;
            }

            .flow-timeline::before,
            .flow-step::before {
                display: none;
            }

            .flow-step {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .flow-step::after {
                display: none;
            }

            .flow-step:nth-child(6) .step-circle {
                width: 62px;
                height: 62px;
                margin-top: 0;
            }

            .flow-card {
                width: 100%;
                min-height: 260px;
            }
        }

        @media (max-width: 768px) {
            .flow-section {
                padding: 56px 0 44px;
            }

            .flow-section::before,
            .flow-section::after {
                width: 82px;
                height: 64px;
                background-size: 18px 18px;
            }

            .flow-header {
                margin-bottom: 32px;
            }

            .flow-header h2 {
                font-size: clamp(1.75rem, 9vw, 2.35rem);
                line-height: 1.15;
            }

            .flow-header p {
                font-size: 0.98rem;
            }

            .flow-timeline {
                grid-template-columns: 1fr;
                gap: 18px;
                width: 100%;
                max-width: 620px;
                margin-inline: auto;
            }

            .flow-step {
                display: grid;
                grid-template-columns: 48px minmax(0, 1fr);
                gap: 14px;
                align-items: start;
                padding: 0;
                text-align: left;
            }

            .flow-step:not(:last-child)::after {
                content: "";
                position: absolute;
                top: 52px;
                bottom: -18px;
                left: 23px;
                display: block;
                width: 2px;
                height: auto;
                transform: none;
                border-radius: 999px;
                background: linear-gradient(180deg, rgba(10, 159, 145, 0.45), rgba(10, 159, 145, 0.12));
            }

            .step-circle {
                display: flex;
                width: 48px;
                height: 48px;
                margin: 0;
                border-width: 4px;
                font-size: 1.05rem;
            }

            .step-circle::before,
            .step-circle::after {
                display: none;
            }

            .flow-step:nth-child(6) .step-circle {
                display: flex;
                width: 48px;
                height: 48px;
            }

            .flow-card {
                display: grid;
                grid-template-columns: 58px minmax(0, 1fr);
                column-gap: 14px;
                row-gap: 2px;
                align-items: center;
                min-height: auto;
                padding: 16px;
                border-radius: 14px;
                text-align: left;
            }

            .flow-card::before {
                display: none;
            }

            .flow-icon {
                grid-column: 1;
                grid-row: 1 / span 2;
                width: 54px;
                height: 54px;
                margin: 0;
                font-size: 1.5rem;
            }

            .flow-section .step-title {
                grid-column: 2;
                min-height: 0;
                margin: 0 0 3px;
                align-self: end;
                text-align: left;
                font-size: 0.98rem;
            }

            .flow-section .step-description {
                grid-column: 2;
                min-height: 0;
                max-width: none;
                margin: 0;
                align-self: start;
                text-align: left;
                font-size: 0.86rem;
                line-height: 1.4;
            }

            .flow-action {
                min-height: 250px;
            }

            .flow-action::before {
                left: 18px;
                bottom: 8px;
            }

            .flow-action::after {
                right: -80px;
                top: 28px;
            }
        }

        /* Responsive Logo Styling */
        .navbar-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        @media (max-width: 768px) {
            .navbar-logo {
                height: 48px;
            }
        }

        @media (max-width: 480px) {
            .navbar-logo {
                height: 40px;
            }
        }
    </style>
</head>

<body>
    <!-- ============ NAVBAR ============ -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}"
                style="font-size: 1.5rem; color: #1D3557;">
                <img src="{{ asset('logo.png') }}" alt="Legal Bruz (LLP) logo" class="navbar-logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar"
                aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="guestNavbar">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-nav-logout" href="{{ route('register') }}">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ============ HERO ============ -->
    <section class="hero-section">
        <div class="container">
            <h1>Complete Trademark Registration Flow</h1>
            <p>Understand every step from signup to registration completion</p>
        </div>
    </section>

    <!-- ============ QUICK FLOW TIMELINE ============ -->
    <section class="flow-section" id="flow">
        <div class="container">
            <div class="flow-header">
                <div class="flow-kicker">
                    <i class="bi bi-stars"></i>
                    <span>8 Simple Steps</span>
                </div>
                <h2><span>8-Step</span> Registration Journey</h2>
                <p>See how your trademark moves from application to registration</p>
            </div>

            <div class="flow-timeline">
                <div class="flow-step">
                    <div class="step-circle">1</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-person-plus"></i></div>
                        <div class="step-title">Sign Up</div>
                        <div class="step-description">Create account & receive welcome email</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">2</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-clipboard-check"></i></div>
                        <div class="step-title">Choose Type</div>
                        <div class="step-description">Individual, Company, or LLP</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">3</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                        <div class="step-title">Payment & Docs</div>
                        <div class="step-description">Pay 50% + view requirements</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">4</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-pencil-square"></i></div>
                        <div class="step-title">Fill Form</div>
                        <div class="step-description">Complete application details</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">5</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-file-earmark-check"></i></div>
                        <div class="step-title">Generate Docs</div>
                        <div class="step-description">Affidavit & POA created</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">6</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="step-title">Admin Review</div>
                        <div class="step-description">Check & filing by admin</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">7</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-cloud-arrow-down"></i></div>
                        <div class="step-title">Download</div>
                        <div class="step-description">Get application document</div>

                    </div>
                </div>
                <div class="flow-step">
                    <div class="step-circle">8</div>
                    <div class="flow-card">
                        <div class="flow-icon"><i class="bi bi-bar-chart-line"></i></div>
                        <div class="step-title">Track Status</div>
                        <div class="step-description">Monitor in dashboard</div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ============ DETAILED FLOW ============ -->
    <section style="padding: 100px 0; background: var(--light-bg);">
        <div class="container">
            <div class="section-title">
                <h2>Detailed Step-by-Step Guide</h2>
                <p>Complete information about each stage of the registration process</p>
            </div>

            <div class="detailed-steps">
                <!-- Step 1 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">1</div>
                        <div class="step-header-content">
                            <h3>User Signup & Welcome</h3>
                            <p>Account creation and email verification</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>New users register with their email and password. An automatic welcome email is sent
                            confirming their registration.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>What Happens</h5>
                            <ul>
                                <li>Account created in system</li>
                                <li>Verification email sent</li>
                                <li>Welcome message displayed</li>
                                <li>User dashboard ready</li>
                            </ul>
                        </div>
                        <span class="actor-badge user">User Action</span>
                        <span class="actor-badge system" style="margin-left: 10px;">System Automated</span>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">2</div>
                        <div class="step-header-content">
                            <h3>Choose Entity Type</h3>
                            <p>Select business structure for registration</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>User selects the appropriate entity type based on their business structure. This affects KYC
                            requirements and documentation.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Available Options</h5>
                            <ul>
                                <li><strong>Individual</strong> - Self-employed professionals & solo entrepreneurs</li>
                                <li><strong>Company</strong> - Registered businesses & corporations</li>
                                <li><strong>LLP</strong> - Limited Liability Partnerships</li>
                            </ul>
                        </div>
                        <span class="actor-badge user">User Action</span>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">3</div>
                        <div class="step-header-content">
                            <h3>Payment & Document Requirements</h3>
                            <p>50% advance payment and document checklist</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>User reviews required documents and makes 50% advance payment. The system displays a clear
                            checklist of what documents are needed based on entity type.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>For Individuals</h5>
                            <ul>
                                <li>PAN Card</li>
                                <li>Address Proof (Aadhar/Passport)</li>
                                <li>Email & Phone verification</li>
                                <li>Trademark Logo (if any)</li>
                            </ul>
                        </div>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>For Companies</h5>
                            <ul>
                                <li>Certificate of Incorporation</li>
                                <li>PAN Certificate</li>
                                <li>GST Certificate (if available)</li>
                                <li>Authorized Signatory ID Proof</li>
                                <li>Trademark Logo</li>
                            </ul>
                        </div>
                        <div class="info-box">
                            <h5>💰 Payment Details</h5>
                            @php
                                $flowTrademarkPrices = \App\Models\TrademarkPricing::activePlans();
                                $flowTrademarkDefaults = \App\Models\TrademarkPricing::defaults();
                                $flowIndividualPrice = (float) ($flowTrademarkPrices['individual']['amount'] ?? $flowTrademarkDefaults['individual']['amount']);
                                $flowCompanyPrice = (float) ($flowTrademarkPrices['company']['amount'] ?? $flowTrademarkDefaults['company']['amount']);
                            @endphp
                            <p><strong>₹{{ number_format($flowIndividualPrice, 0) }}</strong> for Individual | <strong>₹{{ number_format($flowCompanyPrice, 0) }}</strong> for Company <b>/</b> LLP
                                <b>/</b> Partnership <b>/</b> NGO. (50% advance)
                            </p>
                        </div>
                        <span class="actor-badge user">User Action</span>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">4</div>
                        <div class="step-header-content">
                            <h3>Fill Application Form</h3>
                            <p>Complete trademark registration details</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>User fills comprehensive application form with trademark details, business information, and
                            classification. The form saves automatically as they progress.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Form Sections</h5>
                            <ul>
                                <li>Trademark name/mark description</li>
                                <li>Goods & services classification (Nice classes)</li>
                                <li>Business details & address</li>
                                <li>Applicant information</li>
                                <li>Contact details for correspondence</li>
                            </ul>
                        </div>
                        <span class="actor-badge user">User Action</span>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">5</div>
                        <div class="step-header-content">
                            <h3>Generate Affidavit & POA</h3>
                            <p>Auto-generate legal documents after form submission</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>After form submission, system automatically generates Affidavit (sworn statement) and Power
                            of Attorney (POA) documents based on the information provided. User can review and download.
                        </p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Generated Documents</h5>
                            <ul>
                                <li>Affidavit (sworn statement about trademark ownership)</li>
                                <li>Power of Attorney (authorization to our legal team)</li>
                                <li>Declaration forms</li>
                                <li>All formatted as per IPO requirements</li>
                            </ul>
                        </div>
                        <span class="actor-badge system">System Automated</span>
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">6</div>
                        <div class="step-header-content">
                            <h3>Admin Review & Filing</h3>
                            <p>Expert verification and official submission to IPO</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>Our expert team reviews all documents for completeness and accuracy. Once verified,
                            application is officially filed with Indian Patent Office (IPO) on behalf of the applicant.
                        </p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Admin Verification</h5>
                            <ul>
                                <li>Check all documents for completeness</li>
                                <li>Verify trademark eligibility</li>
                                <li>Ensure correct classification</li>
                                <li>Review legal requirements</li>
                                <li>File with IPO</li>
                            </ul>
                        </div>
                        <div class="info-box">
                            <h5>⏱️ Timeline</h5>
                            <p>Usually completed within 24-48 hours of document upload</p>
                        </div>
                        <span class="actor-badge admin">Admin Action</span>
                    </div>
                </div>

                <!-- Step 7 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">7</div>
                        <div class="step-header-content">
                            <h3>Download Application</h3>
                            <p>Client receives official application document</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>After admin filing, user can download the official application document and filing receipt.
                            This document is essential for tracking and can be saved for records.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Available Downloads</h5>
                            <ul>
                                <li>Official application receipt from IPO</li>
                                <li>Filing confirmation document</li>
                                <li>Application reference number</li>
                                <li>Filing date & timeline</li>
                            </ul>
                        </div>
                        <span class="actor-badge user">User Action</span>
                    </div>
                </div>

                <!-- Step 8 -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number-badge">8</div>
                        <div class="step-header-content">
                            <h3>Track Status in Dashboard</h3>
                            <p>Real-time monitoring and updates on registration progress</p>
                        </div>
                    </div>
                    <div class="step-content">
                        <p>User can log into their dashboard anytime to check the current status of their trademark
                            application. Real-time updates from IPO are automatically reflected.</p>
                        <div class="requirements-list">
                            <h5><i class="bi bi-check-circle" style="margin-right: 8px;"></i>Dashboard Shows</h5>
                            <ul>
                                <li>Current application status</li>
                                <li>Examination report (when available)</li>
                                <li>Any IPO queries or objections</li>
                                <li>Timeline and next steps</li>
                                <li>All uploaded documents</li>
                                <li>Payment history</li>
                            </ul>
                        </div>
                        <div class="info-box">
                            <h5>📊 Registration Timeline</h5>
                            <p><strong>6-12 months</strong> - Complete registration process (subject to examination &
                                opposition period)</p>
                        </div>
                        <span class="actor-badge system">System Live Updates</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ COMPLETE TIMELINE VIEW ============ -->
    <section class="complete-timeline-section" style="padding: 100px 0; background: var(--white);">
        <div class="container">
            <div class="section-title">
                <h2>Complete Timeline View</h2>
                <p>Visual representation of the entire registration journey</p>
            </div>

            <div class="timeline-visual">
                <div class="timeline-line"></div>

                <div class="timeline-item timeline-item-right">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>🎯 Day 1: Registration & Preparation</h4>
                        <p>-> User signup and entity type selection</p>
                        <p>-> KYC verification and document submission</p>
                        <p>-> Payment and form filling</p>
                    </div>
                </div>

                <div class="timeline-item timeline-item-left">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>📝 Day 2: Document Generation & Admin Filing</h4>
                        <p>-> Affidavit & POA auto-generation</p>
                        <p>-> Admin review and official IPO filing</p>
                    </div>
                </div>

                <div class="timeline-item timeline-item-right">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>✅ Day 3: Confirmation & Monitoring</h4>
                        <p>-> Download application receipt</p>
                        <p>-> Track status in dashboard</p>
                    </div>
                </div>

                <div class="timeline-item timeline-item-left">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>🎁 3-12 Months: IPO Processing</h4>
                        <p><strong>Examination:</strong> IPO examines your application</p>
                        <p><strong>Opposition Period:</strong> 4-month public notice period</p>
                        <p><strong>Registration:</strong> Certificate issued after 10-year term</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ KEY POINTS ============ -->
    <section style="padding: 100px 0; background: var(--light-bg);">
        <div class="container">
            <div class="section-title">
                <h2>Important Key Points</h2>
                <p>Essential information to know before starting</p>
            </div>

            <div class="row g-4" style="margin-top: 50px;">
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>💡 50% Advance Payment Policy</h5>
                        @php
                            $flowTrademarkPrices = $flowTrademarkPrices ?? \App\Models\TrademarkPricing::activePlans();
                            $flowTrademarkDefaults = $flowTrademarkDefaults ?? \App\Models\TrademarkPricing::defaults();
                            $flowIndividualPrice = $flowIndividualPrice ?? (float) ($flowTrademarkPrices['individual']['amount'] ?? $flowTrademarkDefaults['individual']['amount']);
                            $flowIndividualAdvance = round($flowIndividualPrice * 0.5);
                        @endphp
                        <p>You pay 50% (₹{{ number_format($flowIndividualAdvance, 0) }} for Individual / Proprietor / Trader) in advance. Remaining 50% is paid
                            after admin approval
                            and before or at IPO filing. No filing happens without payment.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>📋 Auto-Generated Documents</h5>
                        <p>Affidavit and POA are auto-generated based on your form inputs. You get to review them before
                            admin submission. All documents follow IPO format requirements.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>⚡ Fast Admin Processing</h5>
                        <p>Admin team reviews and files your application within 24-48 hours. You don't need to manually
                            file anything - we handle IPO filing on your behalf.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>📊 Real-Time Dashboard</h5>
                        <p>Your dashboard shows live updates from IPO. You'll see examination reports, any queries, and
                            exact status at every stage of the process.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>📞 24/7 Support Available</h5>
                        <p>Our expert team is available 24/7 to answer your questions, clarify any confusion, and guide
                            you through the entire process.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <h5>✅ 98% Success Rate</h5>
                        <p>With our expert guidance and proper documentation, 98% of applications are approved. We
                            handle oppositions and queries professionally.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section
        style="padding: 80px 0; background: linear-gradient(135deg, var(--navy) 0%, #2d4a73 100%); color: var(--white); text-align: center;">
        <div class="container">
            <h2 style="color: var(--white); font-size: 2.5rem; margin-bottom: 20px;">Ready to Start Your Registration?
            </h2>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 40px;">Join thousands of registered trademarks.
                Complete process in just 48 hours, after complete upload of documents.</p>
            @auth
                <a href="{{ route('trademark.type-selection') }}" class="btn-primary-custom">Start Now</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary-custom">Sign Up Free</a>
            @endauth
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer>
        <p>&copy; 2026 Legal Bruz (LLP) - India's Fastest Trademark Registration Platform</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
