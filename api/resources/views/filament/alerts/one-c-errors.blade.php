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
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 flex-shrink-0" />
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
                    Проверьте мониторинг на Dashboard.
                </p>
            </div>
        </div>
    </div>
@endif
