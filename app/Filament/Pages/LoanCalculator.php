<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;

class LoanCalculator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Loan Calculator';
    protected static ?string $title = 'Loan Calculator';

    protected string $view = 'filament.pages.loan-calculator';

    protected static ?int $navigationSort = 6;

    public ?array $data = [];

    public $loan_amount;
    public $interest_rate;
    public $years;

    public $monthly_payment = 0;
    public $total_interest = 0;
    public $total_paid = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->columns(1)
                ->maxWidth('md')
                ->schema([
                    Forms\Components\TextInput::make('loan_amount')
                        ->label('Loan Amount')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('interest_rate')
                        ->label('Interest Rate (%)')
                        ->numeric()
                        ->suffix('%')
                        ->required(),

                    Forms\Components\TextInput::make('years')
                        ->label('Years')
                        ->numeric()
                        ->suffix('yrs')
                        ->required(),

                    Actions::make([
                        Action::make('calculate')
                            ->label('Calculate')
                            ->action('calculate')
                            ->color('success'),

                        Action::make('clear')
                            ->label('Clear')
                            ->color('gray')
                            ->outlined()
                            ->action('clearForm'),
                    ])
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'mt-6 flex gap-2'])
                ]),
        ];
    }

    public function calculate(): void
    {
        $P = $this->loan_amount;
        $annualRate = $this->interest_rate / 100;
        $monthlyRate = $annualRate / 12;
        $n = $this->years * 12;

        if ($monthlyRate == 0) {
            $monthly = $P / $n;
        } else {
            $monthly = $P * ($monthlyRate * pow(1 + $monthlyRate, $n))
                / (pow(1 + $monthlyRate, $n) - 1);
        }

        $totalPaid = $monthly * $n;
        $totalInterest = $totalPaid - $P;

        $this->monthly_payment = round($monthly, 2);
        $this->total_interest = round($totalInterest, 2);
        $this->total_paid = round($totalPaid, 2);
    }

    public function clearForm(): void
    {
        // Reset inputs
        $this->loan_amount = null;
        $this->interest_rate = null;
        $this->years = null;

        // Reset results
        $this->monthly_payment = 0;
        $this->total_interest = 0;
        $this->total_paid = 0;

        // Reset form state (important for Filament)
        $this->form->fill();
    }
}
