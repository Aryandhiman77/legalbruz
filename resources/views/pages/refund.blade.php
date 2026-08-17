@extends('layouts.app')
@section('title', 'Refund Policy | Legal Bruz')
@section('meta_description', 'Read the Legal Bruz refund policy for consultations, filing assistance, advisory support, fixed-fee packages, and related services.')
@section('canonical_url', route('refund'))
@section('og_title', 'Refund Policy | Legal Bruz')
@section('og_description', 'Refund terms for payments made to Legal Bruz.')

@section('content')
    @include('pages.partials.styles')
    <div class="public-page legal-page">
        <header class="legal-hero">
            <div class="legal-hero-inner">
                <span class="legal-hero-eyebrow">Legal Information</span>
                <h1>{{ $legalPage['title'] ?? 'Refund Policy' }}</h1>
                <p class="legal-hero-copy">Please review this policy before making a payment for Legal Bruz services.</p>
            </div>
        </header>

        <div class="legal-content">
            <article class="legal-card">
                <div class="refund-policy-text">{!! $legalPage['content'] ?? '' !!}</div>
            </article>
        </div>
    </div>

    <style>
        .refund-policy-text {
            white-space: pre-wrap;
            color: #26364f;
            font-size: 1rem;
            line-height: 1.8;
        }
        .refund-policy-text :is(p, ul, ol, blockquote, table) {
            margin-bottom: 1rem;
        }
    </style>
@endsection
