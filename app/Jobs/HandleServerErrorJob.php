<?php

namespace App\Jobs;

use App\Actions\HandleErrorLoggingAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class HandleServerErrorJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct (public Throwable $e, public string $message, public array|null $data) {}

    /**
     * Execute the job.
     */
    public function handle (): void
    {
        HandleErrorLoggingAction::handle($this->e, $this->message, $this->data);
    }
}