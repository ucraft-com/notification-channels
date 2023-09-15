<?php

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
     * @var string|null
     */
    protected ?string $senderName;

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
        $this->topicName = config('notification-channels.topic_name');
    }

    /**
     * Send the message to kafka.
     *
     * @param array $payload
     *
     * @return void
     */
    protected function dispatchMessage(array $payload): void
    {
        $body = [
            'type'    => $this->getType(),
            'payload' => $payload,
        ];

        $message = $this->builder
            ->setTopicName($this->topicName)
            ->setKey($this->senderName)
            ->setBody($body)
            ->getMessage();

        $this->dispatcher->dispatch(new ProduceMessageEvent($message));
    }
}
