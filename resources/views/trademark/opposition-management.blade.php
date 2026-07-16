@extends('layouts.app')

@section('content')
    <style>
        .opposition-page {
            --opposition-navy: #08245a;
            --opposition-text: #2e3954;
            --opposition-muted: #5b6680;
            --opposition-blue: #2563eb;
            --opposition-blue-dark: #1d4ed8;
            --opposition-green: #24963d;
            --opposition-green-dark: #1f8736;
            --opposition-border: #d9e3f3;
            margin-top: -40px;
            min-height: calc(100vh - 118px);
            padding: 26px 18px 36px;
            background:
                radial-gradient(circle at 18% 20%, rgba(37, 99, 235, 0.06), transparent 280px),
                radial-gradient(circle at 82% 25%, rgba(36, 150, 61, 0.07), transparent 300px),
                linear-gradient(180deg, #f8fbff 0%, #ffffff 38%, #f8fbff 100%);
            color: var(--opposition-text);
        }

        .opposition-shell {
            width: min(1200px, calc(100% - 40px));
            margin: 0 auto;
            padding: 0;
        }

        .opposition-intro {
            max-width: 840px;
            margin: 0 auto 24px;
            text-align: center;
        }

        .opposition-intro h2 {
            margin: 0;
            color: var(--opposition-navy);
            font-size: clamp(2rem, 3.4vw, 2.9rem);
            line-height: 1.12;
            letter-spacing: 0;
        }

        .opposition-mark {
            width: 66px;
            height: 3px;
            margin: 14px auto 14px;
            border-radius: 999px;
            background: #84a7f5;
        }

        .opposition-intro p {
            margin: 0;
            color: var(--opposition-text);
            font-size: clamp(1rem, 1.55vw, 1.22rem);
            line-height: 1.42;
        }

        .opposition-options {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 72px minmax(0, 1fr);
            gap: 34px;
            align-items: center;
            justify-content: center;
            margin-top: 24px;
        }

        .opposition-card {
            width: 100%;
            aspect-ratio: auto;
            min-height: 422px;
            padding: 24px 40px 30px;
            border: 1px solid var(--opposition-border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 16px 30px rgba(8, 36, 90, 0.07);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .opposition-card--defend {
            background:
                radial-gradient(circle at 20% 0%, rgba(37, 99, 235, 0.08), transparent 230px),
                rgba(255, 255, 255, 0.92);
        }

        .opposition-card--oppose {
            background:
                radial-gradient(circle at 18% 0%, rgba(36, 150, 61, 0.09), transparent 230px),
                rgba(255, 255, 255, 0.92);
        }

        .opposition-card-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .opposition-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            margin: 0;
            border: 2px solid #cbd9f8;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.88);
            color: var(--opposition-blue);
            font-size: 2.25rem;
        }

        .opposition-card--oppose .opposition-icon {
            border-color: #cfe4d4;
            color: var(--opposition-green);
        }

        .opposition-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            width: fit-content;
            min-height: 28px;
            margin: 0;
            padding: 3px 14px;
            flex: 0 0 auto;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--opposition-blue), var(--opposition-blue-dark));
            color: #fff;
            font-weight: 900;
            font-size: 0.82rem;
            line-height: 1;
        }

        .opposition-card--oppose .opposition-pill {
            background: linear-gradient(135deg, var(--opposition-green), var(--opposition-green-dark));
        }

        .opposition-card h3 {
            max-width: 430px;
            min-height: 62px;
            margin: 0 auto 14px;
            color: #071f56;
            font-size: clamp(1.12rem, 1.7vw, 1.42rem);
            line-height: 1.28;
            letter-spacing: 0;
        }

        .opposition-rule {
            height: 1px;
            margin: 0 0 16px;
            background: #dfe5ee;
        }

        .opposition-card p {
            min-height: 78px;
            margin: 0 auto 24px;
            color: var(--opposition-text);
            font-size: 1rem;
            line-height: 1.42;
        }

        .opposition-card strong {
            color: #20283d;
            font-weight: 900;
        }

        .opposition-action {
            display: grid;
            grid-template-columns: 34px 1fr 34px;
            align-items: center;
            width: 100%;
            min-height: 58px;
            padding: 0 24px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--opposition-blue), #1455f5);
            color: #fff;
            text-decoration: none;
            font-size: clamp(1rem, 1.55vw, 1.22rem);
            font-weight: 700;
            box-shadow: 0 12px 20px rgba(37, 99, 235, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .opposition-card--oppose .opposition-action {
            background: linear-gradient(135deg, var(--opposition-green), #188235);
            box-shadow: 0 12px 20px rgba(36, 150, 61, 0.2);
        }

        .opposition-action:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 16px 26px rgba(8, 36, 90, 0.2);
        }

        .opposition-action i:first-child {
            font-size: 1.18rem;
        }

        .opposition-action i:last-child {
            justify-self: end;
            font-size: 1.35rem;
        }

        .opposition-divider {
            display: grid;
            grid-template-rows: 1fr auto 1fr;
            align-items: center;
            justify-items: center;
            min-height: 300px;
        }

        .opposition-divider::before,
        .opposition-divider::after {
            content: "";
            width: 2px;
            height: 72px;
            background: #dce2ea;
        }

        .opposition-or {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef1f6;
            color: #2d3548;
            font-size: 1rem;
            font-weight: 900;
        }

        .opposition-help {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 16px;
            align-items: center;
            margin-top: 28px;
            padding: 16px 24px;
            border: 1px solid #c9dafc;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 14px 34px rgba(8, 36, 90, 0.06);
        }

        .opposition-help-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 2px solid var(--opposition-blue);
            border-radius: 50%;
            color: var(--opposition-blue);
            font-size: 1.12rem;
            font-weight: 900;
        }

        .opposition-help p {
            margin: 0;
            color: var(--opposition-text);
            font-size: 0.98rem;
            line-height: 1.4;
        }

        .opposition-help a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 24px;
            border: 1px solid #6d90ff;
            border-radius: 7px;
            color: #1558e8;
            background: #fff;
            text-decoration: none;
            font-size: 0.98rem;
            font-weight: 900;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .opposition-options {
                grid-template-columns: minmax(0, min(100%, 560px));
                gap: 16px;
                justify-content: center;
            }

            .opposition-divider {
                display: flex;
                min-height: auto;
                justify-content: center;
            }

            .opposition-divider::before,
            .opposition-divider::after {
                width: 56px;
                height: 2px;
            }

            .opposition-help {
                grid-template-columns: auto 1fr;
            }

            .opposition-help a {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .opposition-page {
                padding: 14px 12px;
            }

            .opposition-shell {
                width: 100%;
                padding: 0;
            }

            .opposition-card {
                min-height: 0;
                padding: 18px 16px 20px;
            }

            .opposition-card h3,
            .opposition-card p {
                min-height: auto;
            }

            .opposition-action {
                grid-template-columns: 30px 1fr 30px;
                padding: 0 14px;
            }

            .opposition-help {
                gap: 12px;
                padding: 12px 14px;
            }

            .opposition-card-head {
                gap: 10px;
                margin-bottom: 14px;
            }
        }
    </style>

    <main class="opposition-page">
        <section class="opposition-shell" aria-labelledby="opposition-question">
            <div class="opposition-intro">
                <h2 id="opposition-question">What do you need help with?</h2>
                <div class="opposition-mark" aria-hidden="true"></div>
                <p>
                    Trademark Opposition and <strong>Objection Reply</strong> are different legal processes.<br>
                    Please choose the option that best describes your situation.
                </p>
            </div>

            <div class="opposition-options">
                <article class="opposition-card opposition-card--defend">
                    <div class="opposition-card-head">
                        <div class="opposition-icon" aria-hidden="true">
                            <i class="bi bi-shield-fill-check"></i>
                        </div>
                        <span class="opposition-pill">OPTION A</span>
                    </div>
                    <h3>I received a Notice of Opposition against my trademark application</h3>
                    <div class="opposition-rule" aria-hidden="true"></div>
                    <p>
                        Someone has filed an opposition against your trademark application. We will help you
                        <strong>defend your trademark.</strong>
                    </p>
                    <a class="opposition-action" href="{{ auth()->check() ? route('trademark-opposition.create') : route('login') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Defend My Trademark</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </article>

                <div class="opposition-divider" aria-hidden="true">
                    <span class="opposition-or">OR</span>
                </div>

                <article class="opposition-card opposition-card--oppose">
                    <div class="opposition-card-head">
                        <div class="opposition-icon" aria-hidden="true">
                            <i class="bi bi-hammer"></i>
                        </div>
                        <span class="opposition-pill">OPTION B</span>
                    </div>
                    <h3>Someone else has filed a trademark that conflicts with my brand</h3>
                    <div class="opposition-rule" aria-hidden="true"></div>
                    <p>
                        A conflicting trademark has been filed by someone else. We will help you
                        <strong>oppose</strong> that trademark before the Trademark Registry.
                    </p>
                    <a class="opposition-action" href="{{ auth()->check() ? route('trademark-opposition.oppose.create') : route('login') }}">
                        <i class="bi bi-hammer"></i>
                        <span>Oppose a Trademark</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </article>
            </div>

            <aside class="opposition-help">
                <span class="opposition-help-icon" aria-hidden="true">i</span>
                <p>
                    Not sure which option to choose? <strong>Objection Reply</strong> is for notices from the Registry.
                    <strong>Opposition</strong> is filed by a third party. Contact our experts for guidance.
                </p>
                <a href="{{ route('register', ['service' => 'trademark-opposition-consultation']) }}">
                    <i class="bi bi-headset"></i>
                    <span>Talk to an Expert</span>
                </a>
            </aside>
        </section>
    </main>
@endsection
