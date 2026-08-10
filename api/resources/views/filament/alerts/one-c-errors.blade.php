@php
if (! auth()->check()) {
    return;
}

$errorsCount = \App\Models\IntegrationLog::query()
    ->where('created_at', '>=', now()->subDay())
    ->where('status_code', '>=', 400)
    ->count();

$lastError = \App\Models\IntegrationLog::query()
    ->where('status_code', '>=', 400)
    ->orderByDesc('created_at')
    ->first();
@endphp

@if ($errorsCount > 0)
    <div class="fi-banner fi-banner-danger mx-4 mt-4 rounded-lg border border-danger-200 bg-danger-50 p-4 text-danger-600 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-400">
        <div class="flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 flex-shrink-0">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="font-semibold">
                    Ошибки обмена с 1С за последние 24 часа: {{ $errorsCount }}
                </p>
                @if ($lastError)
                    <p class="mt-1 text-sm">
                        Последняя: {{ \Carbon\Carbon::parse($lastError->created_at)->format('d.m.Y H:i') }}
                        — {{ $lastError->endpoint }} (HTTP {{ $lastError->status_code }})
                    </p>
                @endif
                <p class="mt-1 text-sm">
                    <a href="{{ \App\Filament\Resources\IntegrationLogs\IntegrationLogResource::getUrl() }}" class="font-medium underline hover:text-danger-800">
                        Открыть логи обмена с 1С →
                    </a>
                </p>
            </div>
        </div>
    </div>
@endif
