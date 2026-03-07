@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-[1200px] space-y-6">
        <style>
            .docs-shell {
                display: grid;
                gap: 1.25rem;
                grid-template-columns: minmax(0, 1fr);
            }

            @media (min-width: 1024px) {
                .docs-shell {
                    grid-template-columns: minmax(0, 3fr) minmax(240px, 1fr);
                    align-items: start;
                }
            }

            .docs-content h1,
            .docs-content h2,
            .docs-content h3 {
                scroll-margin-top: 96px;
                margin-top: 1.5rem;
                margin-bottom: 0.75rem;
                font-weight: 800;
                color: #0f172a;
            }

            .docs-content h1 {
                font-size: 1.95rem;
            }

            .docs-content h2 {
                font-size: 1.45rem;
            }

            .docs-content h3 {
                font-size: 1.15rem;
            }

            .docs-content p,
            .docs-content li {
                color: #334155;
            }

            .docs-content ul,
            .docs-content ol {
                margin: 0.8rem 0;
                padding-left: 1.3rem;
            }

            .docs-content pre {
                margin: 0.9rem 0;
                overflow-x: auto;
                border-radius: 0.75rem;
                border: 1px solid #1e293b;
                background: #0f172a;
                color: #f8fafc;
                padding: 0.95rem;
                font-size: 0.8rem;
            }

            .docs-content code {
                border-radius: 0.35rem;
                background: #f1f5f9;
                padding: 0.1rem 0.35rem;
                font-size: 0.8rem;
            }

            .docs-content pre code {
                background: transparent;
                padding: 0;
            }

            .docs-content table {
                width: 100%;
                border-collapse: collapse;
                margin: 0.8rem 0;
            }

            .docs-content th,
            .docs-content td {
                border: 1px solid #e2e8f0;
                padding: 0.55rem 0.7rem;
                text-align: left;
            }

            .docs-toc {
                position: sticky;
                top: 96px;
            }

            .docs-toc-link {
                display: block;
                border-radius: 0.5rem;
                padding: 0.3rem 0.45rem;
                color: #475569;
                text-decoration: none;
                line-height: 1.3;
            }

            .docs-toc-link:hover {
                background: #f1f5f9;
                color: #0f172a;
            }
        </style>

        <div class="glass-card p-8 sm:p-10">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Project Docs</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $title }}</h1>
            <p class="mt-3 text-sm text-slate-600">{{ $description }}</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('docs.documentation') }}" class="action-btn-secondary">Documentation</a>
                <a href="{{ route('docs.changelog') }}" class="action-btn-secondary">Changelog</a>
                <a href="{{ route('docs.lessons') }}" class="action-btn-secondary">What I Learned</a>
            </div>
        </div>

        <div class="docs-shell">
            <article class="glass-card p-8 sm:p-10">
                <div class="docs-content text-sm leading-relaxed text-slate-700">
                    {!! $contentHtml !!}
                </div>
            </article>

            <aside class="glass-card p-6 docs-toc">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">On this page</p>
                <nav class="mt-3 space-y-1 text-sm">
                    @forelse($toc as $item)
                        <a
                            href="#{{ $item['id'] }}"
                            class="docs-toc-link {{ $item['level'] === 3 ? 'ml-3 text-xs' : ($item['level'] === 2 ? 'ml-1' : 'font-semibold text-slate-700') }}"
                        >
                            {{ $item['title'] }}
                        </a>
                    @empty
                        <p class="text-xs text-slate-500">No sections detected.</p>
                    @endforelse
                </nav>
            </aside>
        </div>
    </section>
@endsection
