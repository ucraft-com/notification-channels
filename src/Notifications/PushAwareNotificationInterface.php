<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\Notifications;

use Uc\NotificationChannels\Models\CanReceivePushNotificationInterface;

interface PushAwareNotificationInterface
{
    /**
     * Get the push representation of the notification.
     *
     * @param \Uc\NotificationChannels\Models\CanReceivePushNotificationInterface $notifiable
     *
     * @return array{title: string, body: string, userIds: array{0: int}, url: string}
     */
    public function toPush(CanReceivePushNotificationInterface $notifiable): array;
}
