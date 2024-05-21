<?php

namespace App\Console\Commands;

use App\Models\VerificationCode;
use Illuminate\Console\Command;

class StatusCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:status-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Автоматически изменяет статус кодов, срок действия которых вышел';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //
        VerificationCode::updateExpiredCodesStatus();
        $this->info('Statuses updated successfully');
    }
}
