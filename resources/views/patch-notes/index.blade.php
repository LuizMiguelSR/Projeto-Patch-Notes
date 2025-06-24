@extends('layouts.index')

@section('title', 'ToS Papaya Patch Notes')

@section('showFilters', 'true')

@section('content')
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif
    <div class="container py-4">
        <div class="row g-4 patch-grid">
            @foreach ($patchNotes as $patch)
                @php
                    $hasSkillBalance = Str::contains(Str::lower($patch->content), 'skill balance');
                    $isSkillBalance = $hasSkillBalance ? 'skill-balance' : '';
                    $image = $hasSkillBalance
                        ? asset('images/patch-note-holder2.jpg')
                        : ($patch->image_url ?? asset('images/patch-placeholder.jpg'));
                    $title = $hasSkillBalance ? 'Patch Note Skill Balance' : 'Patch Note';
                    $summaryDate = "📅 Date: " . \Carbon\Carbon::parse($patch->date)->format('d M Y');
                    $summaryStatus = "📌 Status: " . ($patch->status ?? 'Unknown');
                @endphp

                <div class="col-12 col-sm-6 col-md-4 col-lg-3 patch-card-wrapper {{ $isSkillBalance }}">
                    <a href="{{ route('patch-notes.show', $patch->id) }}"
                       class="patch-card w-100"
                       data-skill-balance="{{ $hasSkillBalance ? 'true' : 'false' }}">
                        <div class="patch-thumb">
                            <img src="{{ $image }}" alt="Capa do Patch">
                        </div>
                        <div class="patch-body">
                            <h3 class="patch-title">{{ $title }}</h3>
                            <p class="patch-meta">
                                <span class="patch-date">{{ $summaryDate }}</span>
                                <span class="patch-status">{{ $summaryStatus }}</span>
                            </p>
                            <div class="patch-footer">
                                <span class="patch-category">Read more</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    <script>
        fetch('/cron/import-patches?token={{ env("CRON_SECRET") }}')
            .then(() => console.log('Importação acionada.'))
            .catch(() => console.warn('Erro ao acionar importação.'));
    </script>
@endsection
