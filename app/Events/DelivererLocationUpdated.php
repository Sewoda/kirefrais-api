<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DelivererLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int   $orderId,
        public float $latitude,
        public float $longitude
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('order.' . $this->orderId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'  => $this->orderId,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
