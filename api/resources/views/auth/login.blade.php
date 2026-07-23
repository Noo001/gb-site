@extends('layouts.site')

@section('title', 'Вход — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <div class="auth-card">
                <h1 class="section-title">Вход</h1>
                <form method="POST" action="/login">
                    @csrf
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="input" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" class="input" required>
                    </div>
                    <div class="form-group form-check">
                        <label><input type="checkbox" name="remember"> Запомнить меня</label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
                </form>
                <p style="text-align: center; margin-top: 1rem;">Нет аккаунта? <a href="/register" style="color: var(--accent);">Зарегистрироваться</a></p>
            </div>
        </div>
    </section>
@endsection
