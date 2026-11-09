<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatGroup extends Model
{
    protected $table = 'chat_group';

    protected $fillable = ['group_name', 'description'];

    public function members(): HasMany
    {
        return $this->hasMany(ChatGroupMember::class, 'group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'group_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ChatGroupInvitation::class, 'group_id');
    }

    /**
     * Anggota aktif (left_at NULL) — basis otorisasi akses circle.
     */
    public function activeMembers(): HasMany
    {
        return $this->members()->whereNull('left_at');
    }
}
