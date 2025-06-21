@extends('layouts.edit')

@section('title', 'Editar Patch Note')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="page-header">Editar Patch Note</h2>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $erro)
                                <li>{{ $erro }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('patch-notes.update', $patchNote->id) }}">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="content" id="content" value="{{ $patchNote->content }}">

                    <div class="form-group">
                        <label for="summernote">Conteúdo:</label>
                        <div id="summernote">{!! old('content', $patchNote->content ?? '') !!}</div>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-success">💾 Salvar</button>
                        <a href="{{ route('patch-notes.index') }}" class="btn btn-danger">Cancelar</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                🚪 Sair
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#summernote').summernote({
                height: 300
            });

            $('form').on('submit', function () {
                let html = $('#summernote').summernote('code');
                $('#content').val(html);
            });
        });
    </script>
@endpush
