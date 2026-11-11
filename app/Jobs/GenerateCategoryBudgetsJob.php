<?php

namespace App\Jobs;

use App\Services\CategoryBudgetService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCategoryBudgetsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int    $userId,
        public string $sourceMonth,
        public string $targetMonth,
    )
    {
    }

    public function handle(CategoryBudgetService $service): void
    {
        $service->generate(
            $this->userId,
            Carbon::parse($this->sourceMonth),
            Carbon::parse($this->targetMonth),
        );
    }
}
