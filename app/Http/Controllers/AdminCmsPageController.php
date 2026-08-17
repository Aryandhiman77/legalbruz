<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminCmsPageController extends Controller
{
    public function index(): View
    {
        return view('admin.cms-pages.index', [
            'pages' => CmsPage::legalPages(),
        ]);
    }

    public function edit(string $key): View
    {
        abort_unless(in_array($key, CmsPage::legalKeys(), true), 404);

        CmsPage::ensureDefaults();

        $page = CmsPage::query()->where('key', $key)->first();
        $fallback = CmsPage::findByKey($key);

        return view('admin.cms-pages.edit', [
            'page' => $page,
            'fallback' => $fallback,
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        abort_unless(in_array($key, CmsPage::legalKeys(), true), 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:150000'],
            'is_active' => ['nullable', Rule::in(['1'])],
        ]);

        CmsPage::ensureDefaults();

        CmsPage::query()->updateOrCreate(
            ['key' => $key],
            [
                'title' => $validated['title'],
                'content' => $this->sanitizeContent($validated['content']),
                'is_active' => true,
            ],
        );

        CmsPage::flushPageCache($key);

        return redirect()
            ->route('admin.cms-pages.edit', $key)
            ->with('success', 'CMS page updated successfully.');
    }

    private function sanitizeContent(string $html): string
    {
        $html = preg_replace('#<(script|iframe|object|embed|form|input|button|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#\s+on[a-z]+\s*=\s*([\"\']).*?\1#is', '', $html) ?? $html;
        $html = preg_replace('#\s+(href|src)\s*=\s*([\"\'])\s*javascript:.*?\2#is', ' $1="#"', $html) ?? $html;

        return trim($html);
    }
}
