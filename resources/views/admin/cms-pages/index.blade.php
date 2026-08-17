@extends('layouts.app')

@section('content')
    <div class="cms-admin-page">
        <header class="cms-admin-head">
            <div>
                <span>CMS</span>
                <h1>Legal Page Content</h1>
                <p>Edit Privacy Policy, Terms & Conditions, Refund Policy, and website Disclaimer content.</p>
            </div>
        </header>

        <div class="cms-page-grid">
            @foreach ($pages as $page)
                <a href="{{ route('admin.cms-pages.edit', $page->key) }}" class="cms-page-card">
                    <span class="cms-page-icon"><i class="bi bi-file-earmark-richtext"></i></span>
                    <span class="cms-page-copy">
                        <strong>{{ $page->title }}</strong>
                        <small>{{ $page->key === 'disclaimer' ? 'Popup modal content' : 'Public legal page' }}</small>
                        <x-admin-date-time :value="data_get($page, 'updated_at')" label="Updated" />
                    </span>
                    <i class="bi bi-arrow-up-right cms-page-arrow"></i>
                </a>
            @endforeach
        </div>
    </div>

    <style>
        .cms-admin-page{max-width:1180px;margin:0 auto 36px;padding:0 18px;color:#172b46}
        .cms-admin-head{display:flex;justify-content:space-between;gap:18px;margin-bottom:18px;padding:28px;border-radius:18px;background:linear-gradient(135deg,#071f48,#0d6b69);color:#fff;box-shadow:0 18px 40px rgba(7,31,72,.13)}
        .cms-admin-head span{color:#8de8dc;font-size:.68rem;font-weight:900;letter-spacing:.13em;text-transform:uppercase}
        .cms-admin-head h1{margin:7px 0;color:#fff;font-size:clamp(1.55rem,3vw,2.2rem);font-weight:950}
        .cms-admin-head p{margin:0;color:rgba(255,255,255,.78)}
        .cms-page-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
        .cms-page-card{position:relative;display:flex;align-items:center;gap:14px;min-height:116px;padding:20px 54px 20px 20px;border:1px solid #dfe8f4;border-radius:15px;background:#fff;color:#172b46;text-decoration:none;box-shadow:0 12px 28px rgba(8,36,90,.06);transition:.18s ease}
        .cms-page-card:hover{color:#172b46;border-color:#9cd6cf;transform:translateY(-2px)}
        .cms-page-icon{display:grid;place-items:center;flex:0 0 52px;height:52px;border-radius:13px;background:#e8f8f5;color:#159485;font-size:1.2rem}
        .cms-page-copy{display:block;min-width:0}.cms-page-card strong,.cms-page-card small{display:block}
        .cms-page-card strong{font-size:1rem}
        .cms-page-card small{margin-top:4px;color:#66758b;font-size:.78rem}
        .cms-page-arrow{position:absolute;top:18px;right:18px;color:#9aabba}
        @media(max-width:760px){.cms-page-grid{grid-template-columns:1fr}}
    </style>
@endsection
