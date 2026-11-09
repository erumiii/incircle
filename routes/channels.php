<?php

use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| Otorisasi channel privat: hanya anggota AKTIF circle yang boleh
| menerima event pesan (keberadaan circle tidak bocor ke non-anggota,
| konsisten dengan Gate 404 di controller).
*/

Broadcast::channel('circle.{id}', function (User $user, int $id) {
    return ChatGroup::query()
        ->where('id', $id)
        ->whereHas('activeMembers', fn ($q) => $q->where('user_id', $user->id))
        ->exists();
});
