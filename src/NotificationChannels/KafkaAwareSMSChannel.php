<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\NotificationChannels;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\AnonymousNotifiable;
use Uc\KafkaProducer\MessageBuilder;
use Uc\NotificationChannels\Models\CanReceiveSMSNotificationInterface;
use Uc\NotificationChannels\Notifications\SMSAwareNotificationInterface;

use function config;

class KafkaAwareSMSChannel extends KafkaAwareChannel
{
    public function __construct(MessageBuilder $builder, Dispatcher $dispatcher)
    {
        parent::__construct($builder, $dispatcher);

        $this->senderName = config('notification-channels.sms_sender_name');
    }

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

        if ($notifiable instanceof AnonymousNotifiable) {
            $phone = $notifiable->routeNotificationFor('sms');
        } else {
            $phone = $notifiable->getSMSIdentifierAttribute();
        }

        $body = [
            'from'       => $this->senderName,
            'recipients' => [$phone],
            'body'       => $content,
        ];

        $this->dispatchMessage($body);
    }

    /**
     * @inheritDoc
     */
    protected function getType(): string
    {
        return 'sms';
    }
}
