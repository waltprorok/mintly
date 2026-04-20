<?php

namespace App\Filament\Pages;

use App\Mail\SupportRequestMail;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class Support extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-envelope';

    protected static string|null|\UnitEnum $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Support';
    protected static ?string $title = 'Support';
    protected static ?int $navigationSort = 91;

    protected string $view = 'filament.pages.support';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('subject')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('message')
                ->required()
                ->rows(8),

            Forms\Components\FileUpload::make('screenshots')
                ->label('Screenshots')
                ->disk('public')
                ->image()
                ->multiple()
                ->maxFiles(3)
                ->directory('support-uploads')
                ->preserveFilenames()
                ->previewable()
                ->downloadable(),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        Mail::to(config('support.email'))
            ->send(new SupportRequestMail($data));

        Notification::make()
            ->title('Message sent!')
            ->success()
            ->send();

        $this->data = [];
        $this->form->fill();
    }
}
