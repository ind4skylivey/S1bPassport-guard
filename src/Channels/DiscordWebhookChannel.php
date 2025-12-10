<?php

namespace S1bTeam\PassportGuard\Channels;

use Illuminate\Notifications\Notification;

class DiscordWebhookChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toDiscord')) {
            return;
        }

        /** @var mixed $notification */
        $notification->toDiscord($notifiable);
    }
}
