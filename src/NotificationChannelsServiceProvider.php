<?php

declare(strict_types=1);

namespace Uc\NotificationChannels;

use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Uc\KafkaProducer\MessageBuilder;
use Uc\NotificationChannels\Transports\KafkaTransport;
use Uc\NotificationChannels\NotificationChannels\KafkaAwarePushChannel;
use Uc\NotificationChannels\NotificationChannels\KafkaAwareSMSChannel;

class NotificationChannelsServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'notification-channels');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__.'/../config/config.php' => config_path('notification-channels.php'),
                ],
                'notification-channels-config'
            );
        }

        // Register mail transport
        Mail::extend('kafka-mailer', function ($config) {
            return $this->app->make(KafkaTransport::class, ['topic' => $config['topic']]);
        });

        // Register sms notification channel
        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('sms', function ($app) {
                return new KafkaAwareSMSChannel(
                    $app->get(MessageBuilder::class),
                    $app->get(Dispatcher::class),
                );
            });
        });

        // Register push notification channel
        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('push', function ($app) {
                return new KafkaAwarePushChannel(
                    $app->get(MessageBuilder::class),
                    $app->get(Dispatcher::class),
                );
            });
        });
    }
}
