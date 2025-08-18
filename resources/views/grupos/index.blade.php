@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Grupos</h2>
    <a href="{{ route('grupos.create') }}" class="btn btn-primary mb-3">Novo Grupo</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="list-group">
        @foreach($grupos as $grupo)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $grupo->name }}

                <div>
                    <a href="{{ route('grupos.users', $grupo->id) }}" class="btn btn-sm btn-primary">Ver utilizadores</a>

                    <form action="{{ route('grupos.destroy', $grupo) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endsection
