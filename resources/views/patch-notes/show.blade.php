@extends('layouts.index')

@section('title', 'ToS Papaya Patch Notes - Data '.  \Carbon\Carbon::parse($patchNote->date)->format('d \d\e M \d\e Y'))
@section('back-button')
    <a href="{{ route('patch-notes.index') }}" class="back-btn">⬅️ Back</a>
@endsection
@section('content')
    <div class="patch-detail">
        <div class="patch-detail-content">
            {!! $patchNote->content !!}
        </div>
        @auth
            <a href="{{ route('patch-notes.edit', $patchNote->id) }}" class="btn-edit">
                ✏️ Editar
            </a>
        @endauth
        <a href="{{ route('patch-notes.index') }}" class="back-btn">⬅️ Back</a>
    </div>

    <button class="scroll-top-btn" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">⬆️ Top</button>
@endsection
