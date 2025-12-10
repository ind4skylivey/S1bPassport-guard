<?php

namespace S1bTeam\PassportGuard\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class SlackWebhookChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSlack')) {
            return;
        }

        /** @var mixed $notification */
        $notification->toSlack($notifiable);
    }
}
