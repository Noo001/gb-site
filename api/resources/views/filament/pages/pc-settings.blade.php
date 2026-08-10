<x-filament-panels::page>
    <style>
        .fi-page svg {
            max-width: 1.25rem;
            max-height: 1.25rem;
        }
    </style>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                Сохранить
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
