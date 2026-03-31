<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQQueuePublisher implements QueuePublisher
{
    private AMQPStreamConnection $connection;
    private $channel;
    private string $queue;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password,
        string $queue = 'orders'
    ) {
        $this->connection = new AMQPStreamConnection(
            $host,
            $port,
            $user,
            $password
        );

        $this->channel = $this->connection->channel();
        $this->queue = $queue;

        // garante que a fila existe
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    public function publish(object $event): void
    {
        $payload = json_encode($this->serializeEvent($event), JSON_THROW_ON_ERROR);

        $message = new AMQPMessage(
            $payload,
            ['delivery_mode' => 2] // persistente
        );

        $this->channel->basic_publish($message, '', $this->queue);

        echo "[RabbitMQ] Evento publicado na fila {$this->queue}\n";
    }

    private function serializeEvent(object $event): array
    {
        $payload = method_exists($event, 'toArray')
            ? $event->toArray()
            : get_object_vars($event);

        return [
            'type' => method_exists($event, 'eventName')
                ? $event->eventName()
                : get_class($event),
            'event_id' => uniqid('', true),
            'payload' => $payload,
            'created_at' => date('c'),
        ];
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}