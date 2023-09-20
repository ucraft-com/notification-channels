<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\NotificationChannels;

use Illuminate\Events\Dispatcher;
use Uc\KafkaProducer\Events\ProduceMessageEvent;
use Uc\KafkaProducer\MessageBuilder;

use function config;

abstract class KafkaAwareChannel
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected string $topicName;

    /**
     * Get a notification type
     *
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * @param \Uc\KafkaProducer\MessageBuilder $builder
     * @param \Illuminate\Events\Dispatcher    $dispatcher
     */
    public function __construct(
        protected MessageBuilder $builder,
        protected Dispatcher $dispatcher,
    ) {
        $this->topicName = config('notification-channels.topic');
    }

    /**
     * Send the message to kafka.
     *
     * @param array  $request
     * @param string $from
     *
     * @return void
     */
    protected function dispatchMessage(array $request, string $from): void
    {
        $body = [
            [
                'type'    => $this->getType(),
                'request' => $request,
            ],
        ];

        $message = $this->builder
            ->setTopicName($this->topicName)
            ->setKey($from)
            ->setBody($body)
            ->getMessage();

        $this->dispatcher->dispatch(new ProduceMessageEvent($message));
    }
}
