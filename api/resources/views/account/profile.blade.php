@extends('account.layout')

@section('account_title', 'Профиль')

@section('account_content')
    <h1 class="section-title">Личные данные</h1>

    <div class="account-grid">
        <div class="account-card">
            <h2 class="account-card-title">Контактная информация</h2>
            <form method="POST" action="{{ route('account.profile.update') }}" class="account-form">
                @csrf
                <div class="account-form-row">
                    <label class="account-form-label" for="name">Имя</label>
                    <input type="text" id="name" name="name" class="input" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="account-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="account-form-row">
                    <label class="account-form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="input" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="account-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="account-form-row">
                    <label class="account-form-label" for="phone">Телефон</label>
                    <input type="text" id="phone" name="phone" class="input" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <span class="account-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="account-form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>

        <div class="account-card">
            <h2 class="account-card-title">Сменить пароль</h2>
            <form method="POST" action="{{ route('account.password.update') }}" class="account-form">
                @csrf
                <div class="account-form-row">
                    <label class="account-form-label" for="current_password">Текущий пароль</label>
                    <input type="password" id="current_password" name="current_password" class="input" required>
                    @error('current_password')
                        <span class="account-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="account-form-row">
                    <label class="account-form-label" for="password">Новый пароль</label>
                    <input type="password" id="password" name="password" class="input" required>
                    @error('password')
                        <span class="account-form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="account-form-row">
                    <label class="account-form-label" for="password_confirmation">Подтвердите пароль</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input" required>
                </div>

                <div class="account-form-actions">
                    <button type="submit" class="btn btn-primary">Изменить пароль</button>
                </div>
            </form>
        </div>

        <div class="account-card account-card-wide">
            <h2 class="account-card-title">Социальные сети</h2>
            <div class="social-accounts">
                @foreach ($socialProviders as $key => $label)
                    <div class="social-account">
                        <div class="social-account-info">
                            <span class="social-account-name">{{ $label }}</span>
                            @if ($linkedProviders->has($key))
                                <span class="social-account-status social-account-status-linked">Привязано</span>
                            @else
                                <span class="social-account-status">Не привязано</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm {{ $linkedProviders->has($key) ? 'btn-outline' : 'btn-primary' }}">
                            {{ $linkedProviders->has($key) ? 'Привязано' : 'Привязать' }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
