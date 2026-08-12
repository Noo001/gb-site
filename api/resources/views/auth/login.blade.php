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
                        <label>Email или телефон</label>
                        <input type="text" name="login" class="input" value="{{ old('login') }}" required>
                        @error('login')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" class="input" required>
                    </div>
                    <div class="form-group form-check">
                        <label><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Запомнить меня</label>
                    </div>
                    <div class="form-group">
                        <label>Код с картинки</label>
                        <div class="captcha-row">
                            <img src="/captcha" alt="Капча" onclick="this.src='/captcha?'+Date.now()">
                            <input type="text" name="captcha" class="input" placeholder="Введите код" required maxlength="10">
                        </div>
                        @error('captcha')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
                </form>
                <p style="text-align: center; margin-top: 1rem;">Нет аккаунта? <a href="/register" style="color: var(--accent);">Зарегистрироваться</a></p>
            </div>
        </div>
    </section>
@endsection
