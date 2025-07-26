<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\PasswordResetTokenEvent;
use App\Notifications\PasswordResetTokenNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetTokenListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordResetTokenEvent $event): void
    {
        $user = User::where('email', $event->email)->first();
        $user->notify(new PasswordResetTokenNotification($user, $event->token));
    }
}
