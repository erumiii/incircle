<?php

namespace Tests\Feature;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CircleTest extends TestCase
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

    private function joinAs(User $user, ChatGroup $group, string $role = 'member'): ChatGroupMember
    {
        return ChatGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    // F2

    public function test_user_can_create_circle_and_becomes_admin(): void
    {
        $user = $this->makeUser('creator1');

        $response = $this->actingAs($user)->post('/circles', [
            'group_name' => 'Circle Satu',
            'description' => 'deskripsi opsional',
        ]);

        $circle = ChatGroup::where('group_name', 'Circle Satu')->first();
        $this->assertNotNull($circle);
        $response->assertRedirect(route('circles.show', $circle));

        $membership = ChatGroupMember::where('group_id', $circle->id)->first();
        $this->assertSame('admin', $membership->role);
        $this->assertSame($user->id, $membership->user_id);
        $this->assertNotNull($membership->joined_at);
    }

    public function test_circle_name_is_required_and_max_100(): void
    {
        $user = $this->makeUser('creator2');

        $this->actingAs($user)->post('/circles', ['group_name' => ''])
            ->assertSessionHasErrors('group_name');

        $this->actingAs($user)->post('/circles', ['group_name' => Str::repeat('a', 101)])
            ->assertSessionHasErrors('group_name');
    }

    // F5

    public function test_admin_can_edit_circle(): void
    {
        $admin = $this->makeUser('admin1');
        $circle = $this->createCircle($admin, 'Nama Lama');

        $this->actingAs($admin)->patch("/circles/{$circle->id}", [
            'group_name' => 'Nama Baru',
            'description' => 'desc baru',
        ])->assertRedirect(route('circles.show', $circle));

        $this->assertSame('Nama Baru', $circle->refresh()->group_name);
    }

    public function test_member_cannot_edit_circle(): void
    {
        $admin = $this->makeUser('admin2');
        $member = $this->makeUser('member2');
        $circle = $this->createCircle($admin, 'Circle');
        $this->joinAs($member, $circle);

        // non-admin menerima 404, bukan 403: circle tidak bocor
        $this->actingAs($member)->patch("/circles/{$circle->id}", [
            'group_name' => 'Hack',
        ])->assertStatus(404);

        $this->assertSame('Circle', $circle->refresh()->group_name);
    }

    // F6

    public function test_admin_can_delete_circle_with_name_confirmation(): void
    {
        $admin = $this->makeUser('admin3');
        $member = $this->makeUser('member3');
        $circle = $this->createCircle($admin, 'Hapus Saya');
        $this->joinAs($member, $circle);
        $circle->messages()->create(['sender_id' => $admin->id, 'message' => 'hi']);

        $this->actingAs($admin)->delete("/circles/{$circle->id}", ['confirm_name' => 'salah'])
            ->assertSessionHasErrors('confirm_name');
        $this->assertNotNull($circle->fresh());

        $this->actingAs($admin)->delete("/circles/{$circle->id}", ['confirm_name' => 'Hapus Saya'])
            ->assertRedirect(route('circles.index'));

        // cascade: anggota, pesan, undangan ikut terhapus
        $this->assertNull($circle->fresh());
        $this->assertSame(0, ChatGroupMember::where('group_id', $circle->id)->count());
    }

    public function test_member_cannot_delete_circle(): void
    {
        $admin = $this->makeUser('admin4');
        $member = $this->makeUser('member4');
        $circle = $this->createCircle($admin);
        $this->joinAs($member, $circle);

        $this->actingAs($member)->delete("/circles/{$circle->id}", ['confirm_name' => 'Circle'])
            ->assertStatus(404);
        $this->assertNotNull($circle->fresh());
    }

    // F8

    public function test_circle_index_shows_only_active_memberships(): void
    {
        $user = $this->makeUser('indexuser');
        $other = $this->makeUser('otheruser');
        $circle = $this->createCircle($user, 'Mine');
        $otherCircle = $this->createCircle($other, 'Not Mine');

        $response = $this->actingAs($user)->get('/');
        $response->assertOk()->assertSee('Mine')->assertDontSee('Not Mine');

        // keluar (left_at) = hilang dari daftar
        ChatGroupMember::where('group_id', $circle->id)->update(['left_at' => now()]);
        $this->actingAs($user)->get('/')->assertDontSee('Mine');
    }

    // Isolasi (PRD bagian 8 + 11)

    public function test_non_member_gets_404_on_circle_show(): void
    {
        $admin = $this->makeUser('admin5');
        $circle = $this->createCircle($admin, 'Rahasia');
        $outsider = $this->makeUser('outsider5');

        $this->actingAs($outsider)->get("/circles/{$circle->id}")->assertStatus(404);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
