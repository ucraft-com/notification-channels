<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\Notifications;

use Illuminate\Notifications\AnonymousNotifiable;
use Uc\NotificationChannels\Models\CanReceiveSMSNotificationInterface;

interface SMSAwareNotificationInterface
{
    /**
     * @param \Uc\NotificationChannels\Models\CanReceiveSMSNotificationInterface|\Illuminate\Notifications\AnonymousNotifiable $notifiable
     *
     * @return array
     */
    public function toSMS(CanReceiveSMSNotificationInterface|AnonymousNotifiable $notifiable): array;
}
