<x-filament-panels::page>

    <div class="w-full max-w-3xl mx-auto">
        <x-filament::section>
            <x-slot name="heading">
                Delete Account
            </x-slot>

            <x-slot name="description">
                <div class="flex items-center justify-between gap-4">
                    <span>
                        Permanently delete your account if needed.
                    </span>

                    <div class="shrink-0">
                        {{ $this->deleteAccountAction }}
                    </div>
                </div>
            </x-slot>

        </x-filament::section>
    </div>

</x-filament-panels::page>
