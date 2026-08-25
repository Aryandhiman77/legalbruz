<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

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
        $data = $this->validatedData($request);
        $newLogoPath = null;

        if ($request->hasFile('logo')) {
            $newLogoPath = $request->file('logo')->store('customer-reviews/logos', 'public');
            $data['logo_path'] = $newLogoPath;
        }

        try {
            $review = CustomerReview::create($data);
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

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
        $data = $this->validatedData($request);
        $oldLogoPath = $review->logo_path;
        $newLogoPath = null;

        if ($request->hasFile('logo')) {
            $newLogoPath = $request->file('logo')->store('customer-reviews/logos', 'public');
            $data['logo_path'] = $newLogoPath;
        } elseif ($request->boolean('remove_logo')) {
            $data['logo_path'] = null;
        }

        try {
            $review->update($data);
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if (array_key_exists('logo_path', $data) && $oldLogoPath && $oldLogoPath !== $data['logo_path']) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return redirect()
            ->route('admin.reviews.edit', $review)
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(CustomerReview $review): RedirectResponse
    {
        $logoPath = $review->logo_path;
        $review->delete();

        if ($logoPath) {
            Storage::disk('public')->delete($logoPath);
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_title' => ['required', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', Rule::in(['0', '1'])],
            'review' => ['required', 'string', 'max:2000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        unset($data['logo'], $data['remove_logo']);

        return $data;
    }
}
