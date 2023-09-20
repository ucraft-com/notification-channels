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
        SMSAwareNotificationInterface $notification
    ): void {
        $content = $notification->toSMS($notifiable);

        $from = $content['from'];

        unset($content['from']);

        if ($notifiable instanceof AnonymousNotifiable) {
            $phone = $notifiable->routeNotificationFor('sms');
        } else {
            $phone = $notifiable->getSMSIdentifierAttribute();
        }

        $body = [
            'recipients' => [$phone],
            'body'       => $content['body'],
        ];

        $this->dispatchMessage($body, $from);
    }

    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'sms';
    }
}
