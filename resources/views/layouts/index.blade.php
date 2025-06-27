<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ToS Papaya Patch Notes')</title>
    <link rel="icon" href="{{ asset('images/optimizing.png') }}">
    <link rel="stylesheet" href="{{ asset('css/patch-notes.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            font-size: 12px;
            line-height: 1.3;
        }
    </style>
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('patch-notes.index') }}">📋 ToS Papaya Patch Notes</a>
        <div class="d-flex gap-2">
            @auth
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">🚪 Exit</button>
                </form>

                <form method="POST" action="{{ route('patch-notes.import') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">📥 Import</button>
                </form>
            @endauth
            <form action="{{ route('patch-notes.index') }}" method="GET" class="d-flex">
                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm me-2"
                    placeholder="🔍 Search..."
                    value="{{ request('search') }}"
                >
                <button class="btn btn-outline-primary btn-sm" type="submit">Find</button>
            </form>
            <a href="{{ route('patch-notes.calendar') }}" class="btn btn-primary btn-sm">
                🗓️ Calendar
            </a>

            @hasSection('showFilters')
                @if (trim($__env->yieldContent('showFilters')) === 'true')
                    <button id="filter-skill" class="btn btn-primary btn-sm">🔍 Skill Balance</button>
                    <button id="reset-filter" class="btn btn-primary btn-sm">↩️ Show all</button>
                @endif
            @endif
        </div>
    </div>
</nav>

@yield('content')

<button class="scroll-top-btn">⬆️ Top</button>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scrollBtn = document.querySelector('.scroll-top-btn');

        if (scrollBtn) {
            scrollBtn.style.display = 'none';
            window.addEventListener('scroll', () => {
                scrollBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
            });
            scrollBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        const filterBtn = document.getElementById('filter-skill');
        const resetBtn = document.getElementById('reset-filter');

        if (filterBtn && resetBtn) {
            filterBtn.addEventListener('click', () => {
                let hasVisibleCards = false;

                document.querySelectorAll('.patch-card-wrapper').forEach(wrapper => {
                    const card = wrapper.querySelector('.patch-card');
                    const isSkillBalance = wrapper.classList.contains('skill-balance') ||
                        card?.querySelector('.patch-title')?.textContent.toLowerCase().includes('skill balance');

                    if (isSkillBalance) {
                        wrapper.style.display = 'block';
                        hasVisibleCards = true;
                    } else {
                        wrapper.style.display = 'none';
                    }
                });

                const noResultsElement = document.querySelector('.no-results');
                if (!hasVisibleCards) {
                    if (!noResultsElement) {
                        const noResults = document.createElement('div');
                        noResults.className = 'no-results';
                        noResults.textContent = 'No skill balance patches found';
                        document.querySelector('.patch-grid').appendChild(noResults);
                    }
                } else {
                    noResultsElement?.remove();
                }
            });

            resetBtn.addEventListener('click', () => {
                document.querySelectorAll('.patch-card').forEach(card => {
                    card.style.display = 'flex';
                });
                document.querySelectorAll('.patch-card-wrapper').forEach(wrapper => {
                    wrapper.style.display = 'block';
                });
                document.querySelector('.no-results')?.remove();
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
