<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 text-right">
            <x-filament::button type="submit">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament::page> 