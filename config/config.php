<?php

declare(strict_types=1);

return [
    'topic_name'       => env('KAFKA_DOCUMENT_MAIL_TOPIC_NAME'),
    'sms_sender_name'  => env('SMS_SENDER_NAME'),
    'push_sender_name' => env('PUSH_SENDER_NAME'),
];
