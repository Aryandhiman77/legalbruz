<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1" style="color:#1D3557;">{{ $title }}</h1>
            <p class="text-muted mb-0">Published reviews appear immediately in the homepage carousel.</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">Back to Reviews</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST') @method($method) @endif
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label fw-bold">Customer name</label>
                        <input id="customer_name" name="customer_name" type="text"
                            class="form-control @error('customer_name') is-invalid @enderror"
                            value="{{ old('customer_name', $review->customer_name) }}" maxlength="120" required>
                        @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="customer_title" class="form-label fw-bold">Customer title / business</label>
                        <input id="customer_title" name="customer_title" type="text"
                            class="form-control @error('customer_title') is-invalid @enderror"
                            value="{{ old('customer_title', $review->customer_title) }}" maxlength="160" required
                            placeholder="Founder, Tech Startup">
                        @error('customer_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="review" class="form-label fw-bold">Review</label>
                        <textarea id="review" name="review" rows="7"
                            class="form-control @error('review') is-invalid @enderror"
                            maxlength="2000" required>{{ old('review', $review->review) }}</textarea>
                        @error('review') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="rating" class="form-label fw-bold">Rating</label>
                        <select id="rating" name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                            @foreach (range(5, 1) as $rating)
                                <option value="{{ $rating }}" @selected((int) old('rating', $review->rating) === $rating)>
                                    {{ $rating }} {{ Str::plural('star', $rating) }}
                                </option>
                            @endforeach
                        </select>
                        @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="sort_order" class="form-label fw-bold">Display order</label>
                        <input id="sort_order" name="sort_order" type="number"
                            class="form-control @error('sort_order') is-invalid @enderror"
                            value="{{ old('sort_order', $review->sort_order) }}" min="0" max="65535" required>
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                name="is_active" value="1" @checked(old('is_active', $review->is_active))>
                            <label class="form-check-label fw-bold" for="is_active">Published</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white p-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" style="background:#2A9D8F;border:0;">{{ $submitLabel }}</button>
            </div>
        </div>
    </form>
</div>
