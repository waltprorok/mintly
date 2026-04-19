<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Settings extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Account';
    protected static ?string $title = 'Settings';
    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.settings';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deleteAccount')
                ->label('Delete Account')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalHeading('Delete Account')
                ->modalDescription('This will permanently delete your account and all associated data. This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete my account')
                ->form([
                    TextInput::make('password')
                        ->label('Confirm your password')
                        ->password()
                        ->required(),
                ])
                ->action(function (array $data) {

                    $user = auth()->user();

                    // Validate password
                    if (! Hash::check($data['password'], $user->password)) {
                        Notification::make()
                            ->title('Incorrect password')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Logout first
                    Auth::logout();

                    // Delete user (cascades everything)
                    $user->delete();

                    // Clean session
                    session()->invalidate();
                    session()->regenerateToken();

                    // Redirect
                    return redirect('/');
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Account';

    }
}
