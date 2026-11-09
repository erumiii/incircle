<?php

namespace App\Events;

use App\Models\ChatGroupMember;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Notifikasi join: member baru bergabung ke circle, muncul di chat
 * anggota lain secara realtime (gaya "sekelip masuk ruang").
 */
class MemberJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatGroupMember $membership)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('circle.' . $this->membership->group_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'username' => $this->membership->user?->username,
        ];
    }

    public function broadcastAs(): string
    {
        return 'member.joined';
    }
}
