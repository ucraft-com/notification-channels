<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\NotificationChannels;

use Uc\NotificationChannels\Models\CanReceivePushNotificationInterface;
use Uc\NotificationChannels\Notifications\PushAwareNotificationInterface;

class KafkaAwarePushChannel extends KafkaAwareChannel
{
    /**
     * @param \Uc\NotificationChannels\Models\CanReceivePushNotificationInterface   $notifiable
     * @param \Uc\NotificationChannels\Notifications\PushAwareNotificationInterface $notification
     *
     * @return void
     */
    public function send(
        CanReceivePushNotificationInterface $notifiable,
        PushAwareNotificationInterface $notification
    ): void {
        $body = $notification->toPush($notifiable);

        $from = $body['from'];

        unset($body['from']);

        $this->dispatchMessage($body, $from);
    }

    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'push';
    }
}
