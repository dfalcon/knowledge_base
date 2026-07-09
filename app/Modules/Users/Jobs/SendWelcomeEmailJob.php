<?php

namespace App\Modules\Users\Jobs;

use App\Modules\Users\Mail\WelcomeEmail;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [5, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        #[WithoutRelations]
        public User $user
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user)->send(new WelcomeEmail($this->user));
    }

    public function failed(?Throwable $e): void
    {
        Log::error('SendWelcomeEmailJob failed', [
            'user_id' => $this->user->id,
            'error'   => $e?->getMessage(),
        ]);
    }
}
