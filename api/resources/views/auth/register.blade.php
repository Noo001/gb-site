@extends('layouts.site')

@section('title', 'Регистрация — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <div class="auth-card">
                <h1 class="section-title">Регистрация</h1>
                <form method="POST" action="/register">
                    @csrf
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" name="name" class="input" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="input" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" name="phone" class="input" value="{{ old('phone') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" class="input" required>
                    </div>
                    <div class="form-group">
                        <label>Подтвердите пароль</label>
                        <input type="password" name="password_confirmation" class="input" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Зарегистрироваться</button>
                </form>
                <p style="text-align: center; margin-top: 1rem;">Уже есть аккаунт? <a href="/login" style="color: var(--accent);">Войти</a></p>
            </div>
        </div>
    </section>
@endsection
