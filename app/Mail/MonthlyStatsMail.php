<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyStatsMail extends Mailable implements ShouldQueue
{
    use SerializesModels;

    public $user;
    public $income;
    public $expenses;
    public $net;
    public $month;

    public function __construct($user, $income, $expenses, $net, $month)
    {
        $this->user = $user;
        $this->income = $income;
        $this->expenses = $expenses;
        $this->net = $net;
        $this->month = $month;
    }

    public function build(): MonthlyStatsMail
    {
        return $this->subject('Your Monthly Financial Summary')
            ->markdown('emails.monthly-stats');
    }
}
