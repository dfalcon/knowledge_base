<?php

namespace App\Modules\Users\Jobs;

use App\Modules\Users\Mail\AdminWaitApprove;
use App\Modules\Users\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\WithoutRelations;
use Illuminate\Support\Facades\Mail;

class NotifyAdminAboutPendingUserJob implements ShouldQueue
{
    use Queueable;

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
        $admins = User::role('admin')->get();
        Mail::to($admins)->send(new AdminWaitApprove($this->user));
    }
}
