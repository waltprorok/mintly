<x-filament::page>

    <div class="max-w-xl mx-auto">
        <x-filament::section heading="Contact Support">
            <form wire:submit="submit" class="space-y-6">
                {{ $this->form }}

                <br/>

                <x-filament::button type="submit">
                    Send Message
                </x-filament::button>

            </form>

        </x-filament::section>
    </div>

</x-filament::page>
