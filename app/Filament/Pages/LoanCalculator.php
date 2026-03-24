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
    public $extra_payment = 0;
    public $total_paid = 0;

    public $months_saved = 0;
    public $interest_saved = 0;
    public $years_saved = 0;
    public $total_monthly_payment = 0;
    public $total_interest_without_extra = 0;

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

                    Forms\Components\TextInput::make('extra_payment')
                        ->label('Extra Toward Principal')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->helperText('Optional: pay extra toward principal each month'),

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
        $balance = (float) $this->loan_amount;
        $rate = (float) $this->interest_rate;
        $years = (float) $this->years;
        $extra = (float) ($this->extra_payment ?? 0);


        if ($balance <= 0 || $years <= 0) {
            return;
        }

        $monthlyRate = ($rate / 100) / 12;
        $months = $years * 12;

        // Base monthly payment
        if ($monthlyRate == 0) {
            $monthly = $balance / $months;
        } else {
            $monthly = $balance * ($monthlyRate * pow(1 + $monthlyRate, $months))
                / (pow(1 + $monthlyRate, $months) - 1);
        }

        // =========================
        // SCENARIO 1: NORMAL LOAN
        // =========================
        $normalBalance = $balance;
        $normalInterest = 0;
        $normalMonths = 0;

        while ($normalBalance > 0 && $normalMonths < 1000) {
            $interest = $normalBalance * $monthlyRate;
            $principal = $monthly - $interest;

            if ($principal <= 0) break;

            if ($principal > $normalBalance) {
                $principal = $normalBalance;
            }

            $normalBalance -= $principal;
            $normalInterest += $interest;
            $normalMonths++;
        }

        // =========================
        // SCENARIO 2: EXTRA PAYMENT
        // =========================
        $balance = (float) $this->loan_amount; // reset properly

        $totalInterest = 0;
        $totalPaid = 0;
        $actualMonths = 0;

        while ($balance > 0 && $actualMonths < 1000) {
            $interest = $balance * $monthlyRate;
            $principal = $monthly - $interest + $extra;

            if ($principal <= 0) break;

            if ($principal > $balance) {
                $principal = $balance;
            }

            $balance -= $principal;
            $totalInterest += $interest;
            $totalPaid += $principal + $interest;

            $actualMonths++;
        }

        // =========================
        // FINAL RESULTS
        // =========================
        $this->monthly_payment = round($monthly, 2);
        $this->total_interest = round($totalInterest, 2);
        $this->total_paid = round($totalPaid, 2);

        $this->months_saved = max(0, $normalMonths - $actualMonths);
        $this->interest_saved = max(0, round($normalInterest - $totalInterest, 2));

        $this->years_saved = round($this->months_saved / 12, 1);
        $this->total_monthly_payment = round($monthly + $extra, 2);
        $this->total_interest_without_extra = round($normalInterest, 2);
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
        $this->extra_payment = 0;
        $this->total_paid = 0;
        $this->months_saved = 0;
        $this->interest_saved = 0;
        $this->years_saved = 0;
        $this->total_monthly_payment = 0;
        $this->total_interest_without_extra = 0;

        // Reset form state (important for Filament)
        $this->form->fill();
    }
}
