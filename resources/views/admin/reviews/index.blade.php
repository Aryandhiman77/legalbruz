@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1" style="color:#1D3557;">Customer Reviews</h1>
                <p class="text-muted mb-0">Create, order, publish, and update reviews shown on the homepage.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('landing') }}#testimonials" target="_blank" class="btn btn-outline-secondary btn-sm">View Homepage</a>
                <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary btn-sm" style="background:#2A9D8F;border:0;">
                    <i class="bi bi-plus-lg"></i> Add Review
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reviews.index') }}" class="d-flex gap-2 flex-wrap">
                    <input name="search" class="form-control form-control-sm" value="{{ request('search') }}"
                        placeholder="Search customer, title, or review">
                    <select name="status" class="form-select form-select-sm" style="max-width:180px">
                        <option value="">All statuses</option>
                        <x-admin-status-option value="published" label="Published" :selected="request('status') === 'published'" />
                        <x-admin-status-option value="draft" label="Draft" :selected="request('status') === 'draft'" />
                    </select>
                    <button class="btn btn-primary btn-sm" style="background:#2A9D8F;border:0;">Search &amp; filter</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if ($reviews->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle admin-list-table">
                            <thead>
                                <tr>
                                    <th style="width:85px;">Order</th>
                                    <th>Customer</th>
                                    <th>Review</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviews as $review)
                                    <tr>
                                        <td>{{ $review->sort_order }}</td>
                                        <td>
                                            <strong style="color:#1D3557;">{{ $review->customer_name }}</strong>
                                            <div class="small text-muted">{{ $review->customer_title }}</div>
                                        </td>
                                        <td style="min-width:260px;">{{ Str::limit($review->review, 105) }}</td>
                                        <td><span class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}</span></td>
                                        <td><x-admin-status :status="$review->is_active ? 'Published' : 'Draft'" /></td>
                                        <td><x-admin-date-time :value="$review->updated_at" /></td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                                    data-swal-confirm data-swal-title="Delete this review?"
                                                    data-swal-text="This removes it from the homepage."
                                                    data-swal-icon="warning" data-swal-confirm-text="Yes, delete">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $reviews->links() }}
                @else
                    <div class="text-center py-5">
                        <h5>No reviews found</h5>
                        <p class="text-muted">Create a review to feature customer feedback on the homepage.</p>
                        <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary" style="background:#2A9D8F;border:0;">Add Review</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
