<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сайт в режиме разработки — GADGET·BAR</title>
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f0f0f;
            color: #f5f5f5;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .gate {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .gate h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
        }
        .gate p {
            margin: 0 0 1.5rem;
            color: #888;
            font-size: 0.95rem;
        }
        .gate input {
            width: 100%;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 0.5rem;
            color: #f5f5f5;
            font-size: 1rem;
            outline: none;
        }
        .gate input:focus {
            border-color: #0cc0df;
        }
        .gate button {
            width: 100%;
            padding: 0.875rem;
            background: #0cc0df;
            border: none;
            border-radius: 0.5rem;
            color: #0f0f0f;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .gate button:hover {
            background: #09adc8;
        }
        .gate button:disabled {
            opacity: 0.6;
            cursor: wait;
        }
        .error {
            margin-top: 1rem;
            color: #ff5a5a;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="gate" id="gate">
        <h1>Сайт в режиме разработки</h1>
        <p>Введите пароль, чтобы продолжить.</p>
        <form id="access-form" method="POST" action="{{ route('access.check') }}">
            {{-- CSRF исключён для access-check: гейт глобальный, сессии на этом этапе нет --}}
            <input type="password" name="password" id="password" placeholder="Пароль" autofocus required>
            <button type="submit" id="submit-btn">Войти</button>
        </form>
        <p class="error" id="error-message" style="display: none;"></p>
    </div>

    <script>
        (function () {
            const COOKIE_NAME = 'site_access';
            const COOKIE_VALUE = 'granted';
            const STORAGE_KEY = 'site_access_granted';

            function setCookie(name, value, days) {
                const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
                document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
            }

            function hasAccess() {
                return document.cookie.split(';').some(c => c.trim().startsWith(COOKIE_NAME + '=' + COOKIE_VALUE));
            }

            // If the flag is already in cookie, redirect without the query marker.
            if (hasAccess() || sessionStorage.getItem(STORAGE_KEY) === '1') {
                setCookie(COOKIE_NAME, COOKIE_VALUE, 30);
                const url = new URL(window.location.href);
                url.searchParams.delete('site_access');
                window.location.replace(url.toString());
                return;
            }

            const form = document.getElementById('access-form');
            const passwordInput = document.getElementById('password');
            const submitBtn = document.getElementById('submit-btn');
            const errorMessage = document.getElementById('error-message');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                errorMessage.style.display = 'none';
                submitBtn.disabled = true;

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(response => response.json().then(data => ({ ok: response.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            setCookie(COOKIE_NAME, COOKIE_VALUE, 30);
                            sessionStorage.setItem(STORAGE_KEY, '1');
                            const url = new URL(window.location.href);
                            url.searchParams.delete('site_access');
                            window.location.replace(url.toString());
                        } else {
                            throw new Error(data.message || 'Неверный пароль.');
                        }
                    })
                    .catch(error => {
                        errorMessage.textContent = error.message || 'Ошибка проверки пароля.';
                        errorMessage.style.display = 'block';
                        submitBtn.disabled = false;
                        passwordInput.focus();
                    });
            });
        })();
    </script>
</body>
</html>
