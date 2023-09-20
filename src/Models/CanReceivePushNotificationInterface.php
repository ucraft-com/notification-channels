<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\Models;

interface CanReceivePushNotificationInterface extends CanReceiveNotificationsInterface
{
    /**
     * Get id attribute for push notification.
     *
     * @return int
     */
    public function getPushIdentifierAttribute(): int;
}
