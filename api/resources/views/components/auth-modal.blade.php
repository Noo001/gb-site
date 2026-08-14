@php
$hasLoginError = $errors->has('login') || $errors->has('password') || $errors->has('captcha');
$hasRegisterError = $errors->has('name') || $errors->has('email') || $errors->has('phone')
    || $errors->has('password') || $errors->has('password_confirmation')
    || $errors->has('captcha') || $errors->has('privacy') || $errors->has('register');
$startOpen = $hasLoginError || $hasRegisterError ? 'true' : 'false';
$startTab = ($hasRegisterError && ! $hasLoginError) ? 'register' : 'login';
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

<div
    class="auth-modal-overlay"
    x-data="{ open: {{ $startOpen }}, activeTab: '{{ $startTab }}' }"
    x-show="open"
    x-cloak
    @open-auth-modal.window="open = true; activeTab = 'login'"
    @open-register-modal.window="open = true; activeTab = 'register'"
    @keydown.escape.window="open = false"
>
    <div class="auth-modal" @click.outside="open = false">
        <button type="button" class="auth-modal__close" @click="open = false" aria-label="Закрыть">&times;</button>

        <div class="auth-modal__tabs">
            <button
                type="button"
                class="auth-modal__tab"
                :class="{ 'active': activeTab === 'login' }"
                @click="activeTab = 'login'"
            >Вход</button>
            <button
                type="button"
                class="auth-modal__tab"
                :class="{ 'active': activeTab === 'register' }"
                @click="activeTab = 'register'"
            >Регистрация</button>
        </div>

        {{-- Вход --}}
        <div x-show="activeTab === 'login'" x-cloak>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                @error('login')
                    <span class="auth-modal__error">{{ $message }}</span>
                @enderror

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-login">Email или телефон</label>
                    <input
                        id="auth-login"
                        type="text"
                        name="login"
                        class="input"
                        value="{{ old('login') }}"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-password">Пароль</label>
                    <input
                        id="auth-password"
                        type="password"
                        name="password"
                        class="input"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="auth-modal__group auth-modal__check">
                    <label>
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Запомнить меня
                    </label>
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-captcha-login">Код с картинки</label>
                    <div class="auth-modal__captcha">
                        <img src="/captcha" alt="Капча" onclick="this.src='/captcha?'+Date.now()">
                        <input
                            id="auth-captcha-login"
                            type="text"
                            name="captcha"
                            class="input"
                            placeholder="Введите код"
                            required
                            maxlength="10"
                        >
                    </div>
                    @error('captcha')
                        <span class="auth-modal__error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary auth-modal__submit">Продолжить</button>
            </form>

            <div class="auth-modal__switch">
                Нет аккаунта?
                <button
                    type="button"
                    class="auth-modal__link"
                    @click="window.dispatchEvent(new CustomEvent('open-register-modal'))"
                >Зарегистрироваться</button>
            </div>

            <div class="auth-modal__social">
                <button type="button" class="btn btn-social btn-social-yandex" disabled>Войти через Яндекс</button>
                <button type="button" class="btn btn-social btn-social-vk" disabled>Войти через VK</button>
            </div>
        </div>

        {{-- Регистрация --}}
        <div x-show="activeTab === 'register'" x-cloak>
            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                @error('register')
                    <span class="auth-modal__error">{{ $message }}</span>
                @enderror

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-name">ФИО</label>
                    <input
                        id="auth-name"
                        type="text"
                        name="name"
                        class="input"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-email">Email</label>
                    <input
                        id="auth-email"
                        type="email"
                        name="email"
                        class="input"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')
                        <span class="auth-modal__error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-phone">Телефон</label>
                    <input
                        id="auth-phone"
                        type="tel"
                        name="phone"
                        class="input"
                        value="{{ old('phone') }}"
                        required
                    >
                    @error('phone')
                        <span class="auth-modal__error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-register-password">Пароль</label>
                    <input
                        id="auth-register-password"
                        type="password"
                        name="password"
                        class="input"
                        required
                    >
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-password-confirm">Подтвердите пароль</label>
                    <input
                        id="auth-password-confirm"
                        type="password"
                        name="password_confirmation"
                        class="input"
                        required
                    >
                </div>

                <div class="auth-modal__group">
                    <label class="auth-modal__label" for="auth-captcha-register">Код с картинки</label>
                    <div class="auth-modal__captcha">
                        <img src="/captcha" alt="Капча" onclick="this.src='/captcha?'+Date.now()">
                        <input
                            id="auth-captcha-register"
                            type="text"
                            name="captcha"
                            class="input"
                            placeholder="Введите код"
                            required
                            maxlength="10"
                        >
                    </div>
                    @error('captcha')
                        <span class="auth-modal__error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-modal__group auth-modal__check">
                    <label>
                        <input type="checkbox" name="privacy" value="1" {{ old('privacy') ? 'checked' : '' }} required>
                        Согласен с <a href="/privacy" target="_blank" class="auth-modal__link">политикой конфиденциальности</a>
                    </label>
                    @error('privacy')
                        <span class="auth-modal__error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary auth-modal__submit">Зарегистрироваться</button>
            </form>

            <div class="auth-modal__switch">
                Уже есть аккаунт?
                <button
                    type="button"
                    class="auth-modal__link"
                    @click="window.dispatchEvent(new CustomEvent('open-auth-modal'))"
                >Войти</button>
            </div>

            <div class="auth-modal__social">
                <button type="button" class="btn btn-social btn-social-yandex" disabled>Яндекс</button>
                <button type="button" class="btn btn-social btn-social-vk" disabled>VK</button>
            </div>
        </div>
    </div>
</div>
