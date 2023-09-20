<?php

declare(strict_types=1);

return [
    'topic'            => env('MESSENGER_TOPIC'),

    /*
     | In order to use our Kafka-based mail server, put this piece of code in the config/mail.php file.
     |
     'kafka-mailer' => [
          'transport' => 'kafka-mailer,
          'topic'     => env('MESSENGER_TOPIC'),
      ],
      |
     */
];
