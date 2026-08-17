<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Blog::query()->published();

        $featuredPost = (clone $query)
            ->where('is_featured', true)
            ->latest('published_at')
            ->first();

        $posts = $query
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = (string) $request->string('search')->trim();
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Blog::query()
            ->published()
            ->selectRaw('category, count(*) as posts_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('blogs.index', compact('posts', 'featuredPost', 'categories'));
    }

    public function show(Blog $blog): View
    {
        abort_unless($blog->is_public, 404);

        $relatedPosts = Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blogs.show', compact('blog', 'relatedPosts'));
    }
}
