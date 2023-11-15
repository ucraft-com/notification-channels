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
use function is_string;
use function str_starts_with;
use function trim;

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
        Dispatcher               $laravelEventDispatcher,
        MessageBuilder           $builder,
        string                   $topic,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface          $logger = null
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

        if (!$body) {
            return;
        }

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
        $subject = $email->getSubject();
        $subject = is_string($subject) ? trim($subject) : $subject;

        $html = $email->getHtmlBody();
        $html = is_string($html) ? trim($html) : $html;

        if (!$subject || !$html) {
            return [];
        }

        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $name = $attachment->getName();

            $key = str_starts_with($name, 'http') ? 'href' : 'path';

            $attachments[] = [$key => $name];
        }

        $from = $email->getFrom()[0];

        return [
            'type'    => 'mail',
            'request' => [
                'from'        => $from->getAddress(),
                'fromName'    => $from->getName(),
                'recipients'  => array_map(function (Address $address) {
                    return $address->getAddress();
                }, $email->getTo()),
                'subject'     => $subject,
                'attachments' => $attachments,
                'html'        => $html,
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
