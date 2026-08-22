<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = CustomerReview::query()
            ->when($request->filled('status'), fn ($query) => $query->where(
                'is_active',
                $request->string('status')->toString() === 'published'
            ))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_title', 'like', "%{$search}%")
                        ->orWhere('review', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function create(): View
    {
        return view('admin.reviews.create', [
            'review' => new CustomerReview([
                'rating' => 5,
                'sort_order' => (CustomerReview::max('sort_order') ?? 0) + 10,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $review = CustomerReview::create($this->validatedData($request));

        return redirect()
            ->route('admin.reviews.edit', $review)
            ->with('success', 'Review created successfully.');
    }

    public function edit(CustomerReview $review): View
    {
        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, CustomerReview $review): RedirectResponse
    {
        $review->update($this->validatedData($request));

        return redirect()
            ->route('admin.reviews.edit', $review)
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(CustomerReview $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_title' => ['required', 'string', 'max:160'],
            'review' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
