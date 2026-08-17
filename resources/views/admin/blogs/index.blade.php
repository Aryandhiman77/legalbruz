@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1" style="color:#1D3557;">Blog Management</h1>
                <p class="text-muted mb-0">Create, publish, schedule, and optimise website articles.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-outline-secondary btn-sm">View Blog</a>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary btn-sm" style="background:#2A9D8F;border:0;"><i class="fas fa-plus"></i> New Post</a>
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-7"><input name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search title, category, or author"></div>
                <div class="col-md-3"><select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <x-admin-status-option value="draft" label="Draft" :selected="request('status') === 'draft'" />
                    <x-admin-status-option value="published" label="Published" :selected="request('status') === 'published'" />
                    <x-admin-status-option value="scheduled" label="Scheduled" :selected="request('status') === 'scheduled'" />
                </select></div>
                <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary btn-sm flex-grow-1" style="background:#2A9D8F;border:0;">Filter</button>@if(request()->hasAny(['search','status']))<a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>@endif</div>
            </form>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body">
            @if($posts->count())
                <div class="table-responsive"><table class="table table-hover align-middle admin-list-table">
                    <thead><tr><th>Post</th><th>Category</th><th>Author</th><th>Publishing</th><th>SEO</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td><strong style="color:#1D3557;">{{ $post->title }}</strong>@if($post->is_featured)<span class="badge bg-warning text-dark ms-1">Featured</span>@endif<br><small class="text-muted">/{{ $post->slug }}</small></td>
                            <td><span class="badge text-bg-light border">{{ $post->category }}</span></td>
                            <td>{{ $post->author_name }}</td>
                            <td>
                                <x-admin-status :status="$post->is_public ? 'Published' : ($post->status === 'published' ? 'Scheduled' : 'Draft')" />
                                @if($post->published_at)<x-admin-date-time :value="$post->published_at" />@endif
                            </td>
                            <td>
                                <span class="badge {{ $post->seo_title && $post->seo_description ? 'bg-success' : 'bg-warning text-dark' }}">{{ $post->seo_title && $post->seo_description ? 'Metadata set' : 'Metadata automatic' }}</span>
                                <br><span class="badge mt-1 {{ $post->schema_markup ? 'bg-primary' : 'bg-secondary' }}">{{ $post->schema_markup ? 'Schema added' : 'No schema' }}</span>
                            </td>
                            <td><x-admin-date-time :value="$post->updated_at" /></td>
                            <td><div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.blogs.edit',$post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($post->is_public)<a href="{{ route('blog.show',$post) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>@endif
                                <form method="POST" action="{{ route('admin.blogs.destroy',$post) }}" data-swal-confirm data-swal-title="Delete this blog post?" data-swal-text="Images and article content will be permanently removed." data-swal-icon="warning" data-swal-confirm-text="Yes, delete">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                            </div></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
                {{ $posts->links() }}
            @else
                <div class="text-center py-5"><h5>No blog posts found</h5><p class="text-muted">Create your first article to begin publishing.</p><a href="{{ route('admin.blogs.create') }}" class="btn btn-primary" style="background:#2A9D8F;border:0;">Create Post</a></div>
            @endif
        </div></div>
    </div>
@endsection
