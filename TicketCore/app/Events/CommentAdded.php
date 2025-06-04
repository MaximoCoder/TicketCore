<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $ticketId;

    public function __construct($comment, $ticketId)
    {
        $this->comment = $comment;
        $this->ticketId = $ticketId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('ticket.' . $this->ticketId);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->comment->id,
            'comment' => $this->comment->comment,
            'is_private' => $this->comment->is_private,
            'created_at' => $this->comment->created_at,
            'updated_at' => $this->comment->updated_at,
            'user' => [
                'id' => $this->comment->user->id,
                'name' => $this->comment->user->name,
                'email' => $this->comment->user->email
            ]
        ];
    }
}
