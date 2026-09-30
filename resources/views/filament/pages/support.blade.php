<x-filament::page>

    <div class="w-full max-w-3xl mx-auto">
        <x-filament::section heading="Contact Support">
            <form wire:submit="submit" class="space-y-6">
                {{ $this->form }}

                <div class="flex justify-end pt-2">
                    <x-filament::button type="submit">
                        Send
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

</x-filament::page>
