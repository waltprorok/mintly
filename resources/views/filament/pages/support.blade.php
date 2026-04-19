<x-filament::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <br>

        <x-filament::button type="submit">
            Send Message
        </x-filament::button>
    </form>
</x-filament::page>
