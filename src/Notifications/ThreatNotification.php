<?php

namespace S1bTeam\PassportGuard\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use S1bTeam\PassportGuard\Events\ThreatDetected;
use Illuminate\Support\Facades\Http;

class ThreatNotification extends Notification
{
    use Queueable;

    protected ThreatDetected $event;

    public function __construct(ThreatDetected $event)
    {
        $this->event = $event;
    }

    public function via($notifiable)
    {
        $channels = config('s1b-passport-guard.notifications.channels', ['mail']);

        return array_map(function ($channel) {
            return match ($channel) {
                'slack' => \S1bTeam\PassportGuard\Channels\SlackWebhookChannel::class,
                'discord' => \S1bTeam\PassportGuard\Channels\DiscordWebhookChannel::class,
                default => $channel,
            };
        }, $channels);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject('⚠️ Security Alert: ' . ucfirst(str_replace('_', ' ', $this->event->type)))
            ->greeting('Threat Detected!')
            ->line($this->event->message)
            ->line('**Details:**')
            ->line('• Type: ' . $this->event->type)
            ->line('• Client ID: ' . ($this->event->clientId ?? 'N/A'))
            ->line('• User ID: ' . ($this->event->userId ?? 'N/A'))
            ->line('• Timestamp: ' . now()->toDateTimeString())
            ->line('Please investigate this anomaly immediately.')
            ->salutation('S1b Passport Guard');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => $this->event->type,
            'message' => $this->event->message,
            'client_id' => $this->event->clientId,
            'user_id' => $this->event->userId,
            'metadata' => $this->event->metadata,
        ];
    }

    // Custom channel for Slack Webhook
    public function toSlack($notifiable)
    {
        $url = config('s1b-passport-guard.notifications.slack.webhook_url');
        if (!$url) return;

        Http::post($url, [
            'text' => "🚨 *Threat Detected: {$this->event->type}*\n{$this->event->message}",
            'attachments' => [
                [
                    'color' => '#ff0000',
                    'fields' => [
                        ['title' => 'Client ID', 'value' => (string)($this->event->clientId ?? 'N/A'), 'short' => true],
                        ['title' => 'User ID', 'value' => (string)($this->event->userId ?? 'N/A'), 'short' => true],
                    ]
                ]
            ]
        ]);
    }

    // Custom channel for Discord Webhook
    public function toDiscord($notifiable)
    {
        $url = config('s1b-passport-guard.notifications.discord.webhook_url');
        if (!$url) return;

        Http::post($url, [
            'username' => 'S1b Passport Guard',
            'embeds' => [
                [
                    'title' => "⚠️ Threat Detected: {$this->event->type}",
                    'description' => $this->event->message,
                    'color' => 15158332, // Red
                    'fields' => [
                        ['name' => 'Client ID', 'value' => (string)($this->event->clientId ?? 'N/A'), 'inline' => true],
                        ['name' => 'User ID', 'value' => (string)($this->event->userId ?? 'N/A'), 'inline' => true],
                    ],
                    'footer' => ['text' => 'S1b Passport Guard • ' . now()->toDateTimeString()]
                ]
            ]
        ]);
    }
}
