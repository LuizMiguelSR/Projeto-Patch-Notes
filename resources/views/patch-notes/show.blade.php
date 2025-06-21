@extends('layouts.index')

@section('title', 'ToS Papaya Patch Notes - Data ' . \Carbon\Carbon::parse($patchNote->date)->format('d \d\e M \d\e Y'))

@section('content')
    <div class="container-patch">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="patch-detail card shadow-sm">
                    <div class="card-body">
                        <div class="patch-detail-content">
                            {!! $patchNote->content !!}
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2 bg-white border-top-0">
                        <div>
                            <a href="{{ route('patch-notes.index') }}" class="btn btn-outline-secondary btn-sm">
                                ⬅️ Voltar
                            </a>
                        </div>
                        @auth
                            <a href="{{ route('patch-notes.edit', $patchNote->id) }}" class="btn btn-outline-primary btn-sm">
                                ✏️ Editar
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
