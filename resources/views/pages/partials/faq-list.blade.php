@forelse ($faqs as $faq)
    <details class="faq-item">
        <summary>
            <span class="faq-category">{{ $faq->category }}</span>
            {{ $faq->question }}
        </summary>
        <div class="faq-answer">{{ $faq->answer }}</div>
    </details>
@empty
    <div class="faq-empty text-center">
        <h2>{{ request()->filled('search') ? 'No matching FAQs found' : 'No FAQs published yet' }}</h2>
        <p class="mb-3">
            {{ request()->filled('search')
                ? 'Try a different keyword, clear the category filter, or send us your question directly.'
                : 'Our team is updating this section. In the meantime, send us your question directly.' }}
        </p>
        <a class="public-submit d-inline-block text-decoration-none" href="{{ route('contact') }}">Contact us</a>
    </div>
@endforelse
