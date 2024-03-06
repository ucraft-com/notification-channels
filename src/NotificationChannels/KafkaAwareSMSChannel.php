<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\NotificationChannels;

use Illuminate\Notifications\AnonymousNotifiable;
use Uc\NotificationChannels\Models\CanReceiveSMSNotificationInterface;
use Uc\NotificationChannels\Notifications\SMSAwareNotificationInterface;

class KafkaAwareSMSChannel extends KafkaAwareChannel
{
    /**
     * @param \Uc\NotificationChannels\Models\CanReceiveSMSNotificationInterface|\Illuminate\Notifications\AnonymousNotifiable $notifiable
     * @param \Uc\NotificationChannels\Notifications\SMSAwareNotificationInterface                                             $notification
     *
     * @return void
     */
    public function send(
        CanReceiveSMSNotificationInterface|AnonymousNotifiable $notifiable,
        SMSAwareNotificationInterface                          $notification
    ): void {
        $content = $notification->toSMS($notifiable);

        if (!$content) {
            return;
        }

        $hooks = $content['hooks'] ?? null;

        unset($content['hooks']);

        $credentials = $content['credentials'] ?? null;

        unset($content['credentials']);

        if ($notifiable instanceof AnonymousNotifiable) {
            $phone = $notifiable->routeNotificationFor('sms');
        } else {
            $phone = $notifiable->getSMSIdentifierAttribute();
        }

        $body = [
            'recipients' => [$phone],
            'body'       => $content['body'],
        ];

        if ($credentials) {
            $body['credentials'] = $credentials;
        }

        $this->dispatchMessage($body, $phone, $hooks);
    }

    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'sms';
    }
}
