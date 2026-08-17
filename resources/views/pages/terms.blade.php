@extends('layouts.app')
@section('title', 'Terms & Conditions | Legal Bruz')
@section('meta_description', 'Read the terms governing use of the Legal Bruz website and services.')
@section('canonical_url', route('terms'))
@section('og_title', 'Terms & Conditions | Legal Bruz')
@section('og_description', 'Terms governing the Legal Bruz website and services.')

@section('content')
    @include('pages.partials.styles')
    <div class="public-page legal-page">
        <header class="legal-hero">
            <div class="legal-hero-inner">
                <span class="legal-hero-eyebrow">Legal Information</span>
                <h1>{{ $legalPage['title'] ?? 'Terms & Conditions' }}</h1>
                <p class="legal-hero-copy">These terms govern access to and use of the Legal Bruz website and related service requests.</p>
            </div>
        </header>

        <div class="legal-content">
            <article class="legal-card">
                <div class="terms-policy-text">{!! $legalPage['content'] ?? '' !!}</div>
            </article>
        </div>
    </div>

    <style>
        .terms-policy-text {
            white-space: pre-wrap;
            color: #26364f;
            font-size: 1rem;
            line-height: 1.8;
        }
        .terms-policy-text :is(p, ul, ol, blockquote, table) {
            margin-bottom: 1rem;
        }
    </style>
@endsection
