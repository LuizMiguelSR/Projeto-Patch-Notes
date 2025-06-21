<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ToS Papaya Patch Notes')</title>
    <link rel="icon" href="{{ asset('images/optimizing.png') }}">
    <link rel="stylesheet" href="{{ asset('css/patch-notes.css') }}">
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
<nav class="sticky top-0 z-50 bg-white shadow">
    <div class="container-patch flex justify-between items-center px-4 py-3 mx-auto">
        <a href="{{ route('patch-notes.index') }}" class="nav-title">
            📋 ToS Papaya Patch Notes
        </a>

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="filter-btn">🚪 Sair</button>
            </form>

            <form action="{{ route('patch-notes.import') }}" method="POST">
                @csrf
                <button type="submit" class="filter-btn bg-green-600 hover:bg-green-800">
                    📥 Importar
                </button>
            </form>
        @endauth

        @hasSection('showFilters')
            @if (trim($__env->yieldContent('showFilters')) === 'true')
                <button id="filter-skill" class="filter-btn">🔍 Skill Balance</button>
                <button id="reset-filter" class="filter-btn">↩️ Show all</button>
            @endif
        @endif

        @yield('back-button')
        @hasSection('extraButtons')
            @yield('extraButtons')
        @endif
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

                document.querySelectorAll('.patch-card').forEach(card => {
                    const isSkillBalance = card.classList.contains('skill-balance') ||
                        card.querySelector('.patch-title')?.textContent.toLowerCase().includes('skill balance');

                    if (isSkillBalance) {
                        card.style.display = 'flex';
                        hasVisibleCards = true;
                    } else {
                        card.style.display = 'none';
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
                document.querySelector('.no-results')?.remove();
            });
        }
    });
</script>
</body>
</html>
