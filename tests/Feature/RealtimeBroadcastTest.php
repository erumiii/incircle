<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username): User
    {
        return User::create(['username' => $username, 'password' => 'password123']);
    }

    private function createCircle(User $owner, string $name = 'Circle'): ChatGroup
    {
        $group = ChatGroup::create(['group_name' => $name]);
        ChatGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $owner->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return $group;
    }

    public function test_sending_message_broadcasts_on_private_circle_channel(): void
    {
        Event::fake([MessageSent::class]);

        $admin = $this->makeUser('badmin');
        $member = $this->makeUser('bmember');
        $circle = $this->createCircle($admin, 'Bcast Circle');
        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $member->id,
            'role' => 'member', 'joined_at' => now(),
        ]);

        $this->actingAs($member)
            ->post("/circles/{$circle->id}/messages", ['message' => 'pesan realtime']);

        // event realtime ter-dispatch setiap pesan terkirim
        Event::assertDispatched(MessageSent::class, fn (MessageSent $e) => $e->message->group_id === $circle->id
            && $e->message->message === 'pesan realtime');
    }

    public function test_channel_authorization_only_for_active_member(): void
    {
        // driver log: auth() no-op → gunakan assertion closure langsung
        $this->assertChannelAllows('circle.{id}', 'authadmin', 'authoutsider');
    }

    /**
     * Otorisasi channel diverifikasi lewat closure channels.php:
     * anggota aktif lolos, non-anggota & yang dikeluarkan ditolak.
     */
    public function test_channels_closure_authorization(): void
    {
        $admin = $this->makeUser('chadmin');
        $outsider = $this->makeUser('choutsider');
        $kicked = $this->makeUser('chkicked');
        $circle = $this->createCircle($admin, 'Chan Circle');

        $membership = ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $kicked->id,
            'role' => 'member', 'joined_at' => now(),
        ]);
        $membership->update(['left_at' => now()]);

        // anggota aktif: boleh
        $this->assertTrue($this->channelCallbackAllows($admin, $circle->id));
        // non-anggota: ditolak
        $this->assertFalse($this->channelCallbackAllows($outsider, $circle->id));
        // dikeluarkan: ditolak
        $this->assertFalse($this->channelCallbackAllows($kicked, $circle->id));
    }

    private function assertChannelAllows(string $pattern, string $allowedUser, string $deniedUser): void
    {
        $allowed = $this->makeUser($allowedUser);
        $denied = $this->makeUser($deniedUser);
        $circle = $this->createCircle($allowed, 'Chan A');

        $this->assertTrue($this->channelCallbackAllows($allowed, $circle->id));
        $this->assertFalse($this->channelCallbackAllows($denied, $circle->id));
    }

    private function channelCallbackAllows(User $user, int $circleId): bool
    {
        require_once app_path('../routes/channels.php') ?: null;

        // jalankan closure terdaftar lewat BroadcastManager
        $factory = app(\Illuminate\Contracts\Broadcasting\Factory::class);
        $channels = $factory->getChannels();

        // channel circle.{id} wajib terdaftar
        $callback = $channels['circle.{id}'] ?? null;
        $this->assertNotNull($callback, 'channel circle.{id} tidak terdaftar');

        return (bool) (is_string($callback) ? app()->call($callback, ['user' => $user, 'id' => $circleId]) : $callback($user, $circleId));
    }
}
