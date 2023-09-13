<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\NotificationChannels;

use Illuminate\Events\Dispatcher;
use Uc\KafkaProducer\MessageBuilder;
use Uc\NotificationChannels\Models\CanReceivePushNotificationInterface;
use Uc\NotificationChannels\Notifications\PushAwareNotificationInterface;

use function config;

class KafkaAwarePushChannel extends KafkaAwareChannel
{
    public function __construct(MessageBuilder $builder, Dispatcher $dispatcher)
    {
        parent::__construct($builder, $dispatcher);

        $this->senderName = config('notification-channels.push_sender_name');
    }

    /**
     * @param \Uc\NotificationChannels\Models\CanReceivePushNotificationInterface   $notifiable
     * @param \Uc\NotificationChannels\Notifications\PushAwareNotificationInterface $notification
     *
     * @return void
     */
    public function send(CanReceivePushNotificationInterface $notifiable, PushAwareNotificationInterface $notification): void
    {
        $body = $notification->toPush($notifiable);

        $this->dispatchMessage($body);
    }

    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'push';
    }
}
