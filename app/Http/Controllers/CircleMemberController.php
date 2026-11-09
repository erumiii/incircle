<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CircleMemberController extends Controller
{
    /**
     * F7: daftar anggota — username, role, tanggal bergabung (hanya ADMIN).
     */
    public function index(Request $request, ChatGroup $circle): View
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        $members = $circle->members()
            ->whereNull('left_at')
            ->with('user')
            ->orderBy('joined_at')
            ->get();

        return view('circles.members', [
            'circle' => $circle,
            'members' => $members,
        ]);
    }

    /**
     * F7: keluarkan anggota — hanya MEMBER, bukan diri sendiri.
     * left_at terisi; pesan lamanya tetap tampil dengan username pengirim.
     */
    public function kick(Request $request, ChatGroup $circle, ChatGroupMember $member): RedirectResponse
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        if ($member->group_id !== $circle->id || $member->left_at !== null) {
            abort(404);
        }

        if ($member->role === 'admin') {
            return back()->with('error', 'Admins cannot be removed.');
        }

        $member->update(['left_at' => now()]);

        return redirect()->route('circles.members', $circle)->with('status', 'member-kicked');
    }
}
