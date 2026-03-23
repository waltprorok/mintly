<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearLogFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes daily logs and truncates Laravel log';

    const LARAVEL_LOG_PATH = '/storage/logs/laravel.log';
    const LARAVEL_DAILY_LOGS_PATH = '/storage/logs/laravel*.log';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return true
     */
    public function handle(): bool
    {
        $getLogs = glob(base_path() . self::LARAVEL_DAILY_LOGS_PATH);

        $logs = collect($getLogs);

        $bar = $this->output->createProgressBar(count($logs));

        $bar->start();

        foreach ($logs as $log) {
            if ($log == base_path() . self::LARAVEL_LOG_PATH) {
                exec('truncate -s 0 ' . $log); // empties laravel log
            } else {
                exec('rm ' . $log); // remove all logs with a date
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info('');

        return true;
    }
}
