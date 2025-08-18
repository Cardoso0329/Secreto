@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Utilizador</h2>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        @include('users.partials.form')
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
