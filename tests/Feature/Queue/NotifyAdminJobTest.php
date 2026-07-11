<?php

use App\Modules\Users\Jobs\NotifyAdminAboutPendingUserJob;
use App\Modules\Users\Mail\AdminWaitApprove;
use App\Modules\Users\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    seedRoles();
    Mail::fake();
});

it('mails every admin about the pending user', function () {
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');
    $pending = User::factory()->create(['status' => 'pending']);

    (new NotifyAdminAboutPendingUserJob($pending))->handle();

    Mail::assertSent(AdminWaitApprove::class, fn (AdminWaitApprove $mail) => $mail->hasTo($admin->email));
});

it('sends nothing when there are no admins', function () {
    $pending = User::factory()->create(['status' => 'pending']);

    (new NotifyAdminAboutPendingUserJob($pending))->handle();

    Mail::assertNothingSent();
});
