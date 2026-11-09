<?php

namespace Tests\Feature;

use App\Models\ChatGroup;
use App\Models\ChatGroupInvitation;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
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

    public function test_admin_can_create_invitation_and_gets_token(): void
    {
        $admin = $this->makeUser('invadmin');
        $circle = $this->createCircle($admin, 'Undangan');

        $response = $this->actingAs($admin)
            ->post("/circles/{$circle->id}/invitations")
            ->assertRedirect(route('circles.invitations', $circle));

        $invitation = ChatGroupInvitation::where('group_id', $circle->id)->first();
        $this->assertNotNull($invitation);
        $this->assertSame(64, strlen($invitation->token));
        $this->assertNull($invitation->revoked_at);
        $this->assertTrue($invitation->expires_at->diffInDays(now()) <= 7 && $invitation->expires_at->isFuture());
    }

    public function test_member_cannot_create_invitation(): void
    {
        $admin = $this->makeUser('invadmin2');
        $member = $this->makeUser('invmember2');
        $circle = $this->createCircle($admin);
        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $member->id,
            'role' => 'member', 'joined_at' => now(),
        ]);

        $this->actingAs($member)->post("/circles/{$circle->id}/invitations")->assertStatus(404);
        $this->assertSame(0, ChatGroupInvitation::count());
    }

    public function test_user_can_join_via_valid_token_and_becomes_member(): void
    {
        $admin = $this->makeUser('joinadmin');
        $invitee = $this->makeUser('joininvitee');
        $circle = $this->createCircle($admin, 'Joinable');

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
        ]);

        // halaman konfirmasi menampilkan nama circle
        $this->actingAs($invitee)->get("/join/{$invitation->token}")
            ->assertOk()->assertSee('Joinable');

        $this->actingAs($invitee)->post("/join/{$invitation->token}")
            ->assertRedirect(route('circles.show', $circle));

        $membership = ChatGroupMember::where('group_id', $circle->id)
            ->where('user_id', $invitee->id)->first();
        $this->assertNotNull($membership);
        $this->assertSame('member', $membership->role);
    }

    public function test_expired_token_shows_invalid_page(): void
    {
        $admin = $this->makeUser('expadmin');
        $invitee = $this->makeUser('expinvitee');
        $circle = $this->createCircle($admin);

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($invitee)->get("/join/{$invitation->token}")->assertOk()->assertSee('Invalid Invitation');
        $this->actingAs($invitee)->post("/join/{$invitation->token}")
            ->assertRedirect(route('join.show', $invitation->token));
        $this->assertSame(0, ChatGroupMember::where('user_id', $invitee->id)->count());
    }

    public function test_revoked_token_is_invalid(): void
    {
        $admin = $this->makeUser('revadmin');
        $invitee = $this->makeUser('revinvitee');
        $circle = $this->createCircle($admin);

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
            'revoked_at' => now(),
        ]);

        $this->actingAs($invitee)->get("/join/{$invitation->token}")->assertOk()->assertSee('Invalid Invitation');
        $this->assertSame(0, ChatGroupMember::where('user_id', $invitee->id)->count());
    }

    public function test_admin_can_revoke_invitation(): void
    {
        $admin = $this->makeUser('revkadmin');
        $circle = $this->createCircle($admin);

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->delete("/circles/{$circle->id}/invitations/{$invitation->id}")
            ->assertRedirect(route('circles.invitations', $circle));

        $this->assertNotNull($invitation->refresh()->revoked_at);
    }

    public function test_already_member_is_redirected_to_circle(): void
    {
        $admin = $this->makeUser('alreadyadmin');
        $circle = $this->createCircle($admin, 'Already');

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($admin)->get("/join/{$invitation->token}")
            ->assertRedirect(route('circles.show', $circle));
    }

    public function test_kicked_user_can_rejoin_with_new_membership_row(): void
    {
        $admin = $this->makeUser('rejoinadmin');
        $user = $this->makeUser('rejoinuser');
        $circle = $this->createCircle($admin, 'Rejoin');

        ChatGroupMember::create([
            'group_id' => $circle->id, 'user_id' => $user->id,
            'role' => 'member', 'joined_at' => now(), 'left_at' => now(), // dikeluarkan
        ]);

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $admin->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)->post("/join/{$invitation->token}")
            ->assertRedirect(route('circles.show', $circle));

        // baris baru, baris lama tetap tercatat
        $this->assertSame(2, ChatGroupMember::where('user_id', $user->id)->count());
        $this->assertSame(1, ChatGroupMember::where('user_id', $user->id)->whereNull('left_at')->count());
    }
}
