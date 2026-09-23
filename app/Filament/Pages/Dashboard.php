<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        $hasCategories = Category::query()
            ->where('user_id', auth()->id())
            ->exists();

        return [
            CategoryResource::installDefaultsAction()
                ->visible(! $hasCategories),
        ];
    }

    public function mount(): void
    {
        $hasCategories = Category::query()
            ->where('user_id', auth()->id())
            ->exists();

        if (! $hasCategories) {
            $this->mountAction('install_defaults');
        }
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
