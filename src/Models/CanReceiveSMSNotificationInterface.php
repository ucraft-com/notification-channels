<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\Models;

interface CanReceiveSMSNotificationInterface extends CanReceiveNotificationsInterface
{
    /**
     * Get phone number attribute for sms notification.
     *
     * @return string
     */
    public function getSMSIdentifierAttribute(): string;
}
