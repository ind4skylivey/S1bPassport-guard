<?php

namespace S1bTeam\PassportGuard\Listeners;

use Illuminate\Support\Facades\Notification;
use S1bTeam\PassportGuard\Events\ThreatDetected;
use S1bTeam\PassportGuard\Notifications\ThreatNotification;

class SendThreatNotification
{
    public function handle(ThreatDetected $event): void
    {
        if (!config('s1b-passport-guard.notifications.enabled')) {
            return;
        }

        $mailTo = config('s1b-passport-guard.notifications.mail.to');

        // Start routing for mail if configured
        $notifiable = Notification::route('mail', $mailTo);

        // Send the notification
        // The Notification class itself determines active channels via its via() method
        // and handles Slack/Discord webhooks internally via config.
        $notifiable->notify(new ThreatNotification($event));
    }
}
