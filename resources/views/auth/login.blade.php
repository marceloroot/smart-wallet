@extends('layouts.app')

@section('title', 'Entrar')

@section('auth')
    <h1>Entrar</h1>
    <p class="sub">Acesse sua carteira digital</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>

        <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;">
            <input type="checkbox" name="remember" style="width:auto;margin:0;">
            <span style="color:var(--text);">Lembrar-me</span>
        </label>

        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
    </form>

    <p class="form-footer">
        Não tem conta? <a href="{{ route('register') }}">Cadastre-se</a>
    </p>
    <p class="form-footer" style="margin-top:0.5rem;font-size:0.8rem;">
        Demo: teste1@gmail.com / password123
    </p>
@endsection
