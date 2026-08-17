@extends('layouts.app')
@section('title', 'Frequently Asked Questions | Legal Bruz')
@section('meta_description', 'Find answers about Legal Bruz services, trademark registration, applications, payments, and support.')
@section('canonical_url', route('faq'))
@section('og_title', 'Frequently Asked Questions | Legal Bruz')
@section('og_description', 'Clear answers about trademark services, applications, payments, and support.')
@section('head')
    @if (!request()->hasAny(['search', 'category']) && $faqs->isNotEmpty())
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
                ])->values()->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endsection

@section('content')
    @include('pages.partials.styles')
    <div class="public-page faq-page">
        <header class="faq-hero">
            <div class="faq-hero-inner">
                <span class="faq-hero-eyebrow">Help Centre</span>
                <h1>Frequently Asked Questions</h1>
                <p class="faq-hero-copy">Clear answers about our services, trademark applications, payments, and support.</p>

                <form class="faq-search" method="GET" action="{{ route('faq') }}" role="search" data-faq-search-form>
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <label class="visually-hidden" for="faq-search">Search frequently asked questions</label>
                    <i class="bi bi-search faq-search-icon" aria-hidden="true"></i>
                    <input id="faq-search" name="search" type="search" class="faq-search-input"
                        value="{{ request('search') }}" placeholder="Search for answers..." autocomplete="off">
                    <button class="faq-search-clear" type="button" aria-label="Clear FAQ search"
                        data-faq-search-clear @if (!request('search')) hidden @endif>
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                    <span class="faq-search-shortcut" aria-hidden="true">⌘ K</span>
                    <p class="faq-search-status" data-faq-search-status aria-live="polite"></p>
                </form>
            </div>
        </header>

        <section class="faq-content" aria-labelledby="faq-title">
            @if ($categories->isNotEmpty())
                <nav class="faq-toolbar" aria-label="FAQ categories">
                    <a class="faq-chip {{ request('category') ? '' : 'active' }}"
                        href="{{ route('faq', array_filter(['search' => request('search')])) }}">All</a>
                    @foreach ($categories as $category)
                        @php
                            $categoryIcon = match (strtolower($category)) {
                                'applications' => 'bi-file-earmark-text',
                                'general' => 'bi-chat-square',
                                'payments' => 'bi-credit-card',
                                'support' => 'bi-headset',
                                'trademark registration' => 'bi-shield-check',
                                default => 'bi-question-circle',
                            };
                        @endphp
                        <a class="faq-chip {{ request('category') === $category ? 'active' : '' }}"
                            href="{{ route('faq', array_filter(['category' => $category, 'search' => request('search')])) }}">
                            <i class="bi {{ $categoryIcon }}" aria-hidden="true"></i>
                            {{ $category }}
                        </a>
                    @endforeach
                </nav>
            @endif

            <h2 id="faq-title" class="visually-hidden">Questions and answers</h2>
            <div class="faq-list" data-faq-results>
                @include('pages.partials.faq-list', ['faqs' => $faqs])
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('keydown', function (event) {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                document.getElementById('faq-search')?.focus();
            }
        });

        (() => {
            const form = document.querySelector('[data-faq-search-form]');
            const input = document.getElementById('faq-search');
            const results = document.querySelector('[data-faq-results]');
            const clearButton = document.querySelector('[data-faq-search-clear]');
            const status = document.querySelector('[data-faq-search-status]');
            let debounceTimer = null;
            let activeRequest = null;

            if (!form || !input || !results) return;

            const fetchFaqs = async () => {
                if (activeRequest) activeRequest.abort();
                const requestController = new AbortController();
                activeRequest = requestController;

                const params = new URLSearchParams(new FormData(form));
                const url = new URL(form.action, window.location.origin);
                params.forEach((value, key) => {
                    if (String(value).trim() !== '') url.searchParams.set(key, value);
                });

                results.classList.add('is-loading');
                status.textContent = 'Searching…';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: requestController.signal
                    });

                    if (!response.ok) throw new Error('Search request failed');

                    const data = await response.json();
                    results.innerHTML = data.html;
                    status.textContent = `${data.count} ${data.count === 1 ? 'answer' : 'answers'} found`;
                    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        status.textContent = 'Search could not be completed. Press Enter to try again.';
                    }
                } finally {
                    if (activeRequest === requestController) {
                        results.classList.remove('is-loading');
                    }
                }
            };

            input.addEventListener('input', () => {
                clearButton.hidden = input.value.length === 0;
                window.clearTimeout(debounceTimer);
                status.textContent = input.value ? 'Waiting for you to finish typing…' : '';
                debounceTimer = window.setTimeout(fetchFaqs, 350);
            });

            clearButton?.addEventListener('click', () => {
                input.value = '';
                clearButton.hidden = true;
                input.focus();
                window.clearTimeout(debounceTimer);
                fetchFaqs();
            });
        })();
    </script>
@endsection
