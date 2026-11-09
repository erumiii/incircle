<?php

namespace Tests\Feature;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username): User
    {
        return User::create(['username' => $username, 'password' => 'password123']);
    }

    private function setupCircle(): array
    {
        $admin = $this->makeUser('memadmin');
        $member = $this->makeUser('memmember');
        $circle = ChatGroup::create(['group_name' => 'Member Circle']);
        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $admin->id,
            'role' => 'admin', 'joined_at' => now(),
        ]);
        $membership = ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $member->id,
            'role' => 'member', 'joined_at' => now(),
        ]);

        return [$circle, $admin, $member, $membership];
    }

    public function test_admin_sees_member_list(): void
    {
        [$circle, $admin, $member] = $this->setupCircle();

        $this->actingAs($admin)->get("/circles/{$circle->id}/members")
            ->assertOk()
            ->assertSee('memadmin')
            ->assertSee('memmember');
    }

    public function test_member_cannot_see_member_management(): void
    {
        [$circle, , $member] = $this->setupCircle();

        $this->actingAs($member)->get("/circles/{$circle->id}/members")->assertStatus(404);
    }

    public function test_admin_can_kick_member(): void
    {
        [$circle, $admin, $member, $membership] = $this->setupCircle();

        $this->actingAs($admin)->delete("/circles/{$circle->id}/members/{$membership->id}")
            ->assertRedirect(route('circles.members', $circle));

        $this->assertNotNull($membership->refresh()->left_at);
        $this->assertSame(1, ChatGroupMember::where('group_id', $circle->id)->whereNull('left_at')->count());
    }

    public function test_admin_cannot_kick_self_or_another_admin(): void
    {
        [$circle, $admin, , ] = $this->setupCircle();
        $adminMembership = ChatGroupMember::where('user_id', $admin->id)->first();

        // diri sendiri
        $this->actingAs($admin)->delete("/circles/{$circle->id}/members/{$adminMembership->id}")
            ->assertRedirect();
        $this->assertNull($adminMembership->refresh()->left_at);
    }

    public function test_member_cannot_kick_anyone(): void
    {
        [$circle, $admin, $member, $membership] = $this->setupCircle();
        $adminMembership = ChatGroupMember::where('user_id', $admin->id)->first();

        $this->actingAs($member)->delete("/circles/{$circle->id}/members/{$adminMembership->id}")
            ->assertStatus(404);
        $this->assertNull($adminMembership->refresh()->left_at);
    }

    public function test_kicked_members_messages_stay_with_sender_username(): void
    {
        [$circle, $admin, $member, $membership] = $this->setupCircle();
        $circle->messages()->create(['sender_id' => $member->id, 'message' => 'pesan lama']);

        $this->actingAs($admin)->delete("/circles/{$circle->id}/members/{$membership->id}");

        // pesan tetap tampil dengan username pengirim (PRD F7)
        $this->actingAs($admin)->get("/circles/{$circle->id}")
            ->assertOk()
            ->assertSee('pesan lama')
            ->assertSee('memmember');
    }
}
