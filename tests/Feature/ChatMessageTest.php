<?php

namespace Tests\Feature;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username): User
    {
        return User::create(['username' => $username, 'password' => 'password123']);
    }

    private function setupCircle(): array
    {
        $admin = $this->makeUser('msgadmin');
        $member = $this->makeUser('msgmember');
        $circle = ChatGroup::create(['group_name' => 'Chat Circle']);
        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $admin->id,
            'role' => 'admin', 'joined_at' => now(),
        ]);
        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $member->id,
            'role' => 'member', 'joined_at' => now(),
        ]);

        return [$circle, $admin, $member];
    }

    public function test_member_can_send_message(): void
    {
        [$circle, $admin, $member] = $this->setupCircle();

        $this->actingAs($member)->post("/circles/{$circle->id}/messages", ['message' => 'halo semua'])
            ->assertRedirect();

        $this->assertSame(1, $circle->messages()->count());
        $this->assertSame('halo semua', $circle->messages()->first()->message);
        $this->assertSame($member->id, $circle->messages()->first()->sender_id);
    }

    public function test_message_validation_whitespace_and_length(): void
    {
        [$circle, , $member] = $this->setupCircle();

        $this->actingAs($member)->post("/circles/{$circle->id}/messages", ['message' => '   '])
            ->assertSessionHasErrors('message');

        $this->actingAs($member)->post("/circles/{$circle->id}/messages", ['message' => Str::repeat('a', 2001)])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, $circle->messages()->count());
    }

    public function test_non_member_cannot_send_message(): void
    {
        [$circle, , ] = $this->setupCircle();
        $outsider = $this->makeUser('outsidermsg');

        $this->actingAs($outsider)->post("/circles/{$circle->id}/messages", ['message' => 'inject'])
            ->assertStatus(404);

        $this->assertSame(0, $circle->messages()->count());
    }

    public function test_kicked_member_cannot_send_or_read_messages(): void
    {
        [$circle, , $member] = $this->setupCircle();

        ChatGroupMember::where('user_id', $member->id)->update(['left_at' => now()]);

        $this->actingAs($member)->get("/circles/{$circle->id}")->assertStatus(404);
        $this->actingAs($member)->post("/circles/{$circle->id}/messages", ['message' => 'masih bisa?'])
            ->assertStatus(404);
    }

    public function test_messages_render_with_sender_username_oldest_first(): void
    {
        [$circle, $admin, $member] = $this->setupCircle();

        $circle->messages()->create(['sender_id' => $admin->id, 'message' => 'pertama']);
        $circle->messages()->create(['sender_id' => $member->id, 'message' => 'kedua']);

        $response = $this->actingAs($member)->get("/circles/{$circle->id}");
        $response->assertOk();

        $posFirst = strpos($response->getContent(), 'pertama');
        $posSecond = strpos($response->getContent(), 'kedua');
        $this->assertNotFalse($posFirst);
        $this->assertNotFalse($posSecond);
        $this->assertLessThan($posSecond, $posFirst); // lama ke baru

        $response->assertSee('msgmember'); // username pengirim tampil
    }

    public function test_message_is_escaped_against_xss(): void
    {
        [$circle, , $member] = $this->setupCircle();

        $this->actingAs($member)->post("/circles/{$circle->id}/messages", [
            'message' => '<script>alert(1)</script>',
        ]);

        $content = $this->actingAs($member)->get("/circles/{$circle->id}")->getContent();
        $this->assertStringNotContainsString('<script>', $content);
        $this->assertStringContainsString('&lt;script&gt;', $content);
    }
}
