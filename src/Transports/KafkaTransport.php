<?php

declare(strict_types=1);

namespace Uc\NotificationChannels\Transports;

use Illuminate\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Uc\KafkaProducer\Events\ProduceMessageEvent;
use Uc\KafkaProducer\MessageBuilder;

use function array_map;

/**
 * Mailer transport implementation that works with messenger component.
 *
 * @see    https://github.com/ucraft-com/messenger
 *
 * @author Tigran Mesropyan <tiko@ucraft.com>
 */
class KafkaTransport extends AbstractTransport
{
    /**
     * @var \Illuminate\Events\Dispatcher Reference on the instance of Laravel's Event Dispatcher.
     */
    protected Dispatcher $laravelEventDispatcher;

    /**
     * @var \Uc\KafkaProducer\MessageBuilder Reference on the instance of MessageBuilder.
     */
    protected MessageBuilder $builder;

    /**
     * @var string Kafka topic where the e-mail messages should be published.
     */
    protected string $topic;

    public function __construct(
        Dispatcher $laravelEventDispatcher,
        MessageBuilder $builder,
        string $topic,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);

        $this->builder = $builder;
        $this->topic = $topic;
        $this->laravelEventDispatcher = $laravelEventDispatcher;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $body = $this->prepareMessageBody($email);
        $kafkaMessage = $this->builder
            ->setTopicName($this->topic)
            ->setKey($body['request']['from'])
            ->setBody([$body])
            ->getMessage();

        $this->laravelEventDispatcher->dispatch(new ProduceMessageEvent($kafkaMessage));
    }

    /**
     * Prepare Kafka message body.
     *
     * @param \Symfony\Component\Mime\Email $email
     *
     * @return array
     */
    protected function prepareMessageBody(Email $email): array
    {
        $from = $email->getFrom()[0];

        return [
            'type'    => 'mail',
            'request' => [
                'from'       => $from->getAddress(),
                'fromName'   => $from->getName(),
                'recipients' => array_map(function (Address $address) {
                    return $address->getAddress();
                }, $email->getTo()),
                'subject'    => $email->getSubject(),
                'html'       => $email->getHtmlBody(),
            ],
        ];
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'kafka-mailer';
    }
}
