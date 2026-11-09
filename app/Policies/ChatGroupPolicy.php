<?php

namespace App\Policies;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;

/**
 * Otorisasi circle. Semua method mengembalikan false kecuali keanggotaan
 * aktif terbukti; controller mengubahnya menjadi 404 agar keberadaan
 * circle tidak bocor (PRD bagian 8).
 */
class ChatGroupPolicy
{
    /**
     * Keanggotaan aktif user di circle, atau null.
     */
    private function membership(User $user, ChatGroup $group): ?ChatGroupMember
    {
        return ChatGroupMember::query()
            ->where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();
    }

    private function isAdmin(?ChatGroupMember $membership): bool
    {
        return $membership !== null && $membership->role === 'admin';
    }

    /**
     * Melihat circle dan pesan (PRD F4): MEMBER dan ADMIN.
     */
    public function view(User $user, ChatGroup $group): bool
    {
        return $this->membership($user, $group) !== null;
    }

    /**
     * Mengirim pesan (PRD F4): MEMBER dan ADMIN.
     */
    public function sendMessage(User $user, ChatGroup $group): bool
    {
        return $this->membership($user, $group) !== null;
    }

    /**
     * Membuat/mencabut undangan, edit, hapus, kelola anggota (PRD F3/F5/F6/F7): ADMIN.
     */
    public function manage(User $user, ChatGroup $group): bool
    {
        return $this->isAdmin($this->membership($user, $group));
    }

    /**
     * Membuat circle (PRD F2): semua pengguna login.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
