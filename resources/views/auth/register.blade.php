@extends('layouts.app')

@section('title', 'Cadastro')

@section('auth')
    <h1>Criar conta</h1>
    <p class="sub">Cadastre-se e receba sua carteira automaticamente</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="password_confirmation">Confirmar senha</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>

        <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
    </form>

    <p class="form-footer">
        Já tem conta? <a href="{{ route('login') }}">Entrar</a>
    </p>
@endsection
