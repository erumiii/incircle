<?php
namespace App\Http\Controllers;

use App\Events\MemberJoined;

use App\Models\ChatGroup;
use App\Models\ChatGroupInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CircleInvitationController extends Controller
{
    /**
     * F3: daftar undangan circle (hanya ADMIN).
     */
    public function index(Request $request, ChatGroup $circle): View
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        return view('circles.invitations', [
            'circle' => $circle,
            'invitations' => $circle->invitations()->latest('created_at')->get(),
        ]);
    }

    /**
     * F3: buat undangan — token acak 64 hex char, berlaku 7 hari.
     */
    public function store(Request $request, ChatGroup $circle): RedirectResponse
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        $invitation = ChatGroupInvitation::create([
            'group_id' => $circle->id,
            'created_by' => $request->user()->id,
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()
            ->route('circles.invitations', $circle)
            ->with('token', $invitation->token);
    }

    /**
     * F3: cabut undangan.
     */
    public function destroy(Request $request, ChatGroup $circle, ChatGroupInvitation $invitation): RedirectResponse
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        if ($invitation->group_id !== $circle->id) {
            abort(404);
        }

        $invitation->update(['revoked_at' => now()]);

        return redirect()->route('circles.invitations', $circle)->with('status', 'invitation-revoked');
    }

    /**
     * F3: halaman konfirmasi join via token — valid, kedaluwarsa, dicabut, atau sudah anggota.
     */
    public function showJoin(Request $request, string $token): View|RedirectResponse
    {
        $invitation = ChatGroupInvitation::query()
            ->where('token', $token)
            ->first();

        if ($invitation === null || $invitation->revoked_at !== null || $invitation->expires_at->isPast()) {
            return view('join.invalid');
        }

        $circle = $invitation->group;
        $user = $request->user();

        // Sudah anggota aktif: langsung ke circle (PRD F3).
        $activeMembership = $user->memberships()
            ->where('group_id', $circle->id)
            ->whereNull('left_at')
            ->first();

        if ($activeMembership !== null) {
            return redirect()->route('circles.show', $circle);
        }

        return view('join.confirm', [
            'circle' => $circle,
            'token' => $token,
        ]);
    }

    /**
     * F3: proses Gabung — member harus tidak dikenai larangan; user yang pernah
     * keluar/dikeluarkan bergabung dengan baris keanggotaan baru.
     */
    public function join(Request $request, string $token): RedirectResponse
    {
        $invitation = ChatGroupInvitation::query()
            ->where('token', $token)
            ->first();

        if ($invitation === null || $invitation->revoked_at !== null || $invitation->expires_at->isPast()) {
            return redirect()->route('join.show', $token)->with('error', 'Invalid invitation.');
        }

        $circle = $invitation->group;
        $user = $request->user();

        $activeMembership = $user->memberships()
            ->where('group_id', $circle->id)
            ->whereNull('left_at')
            ->first();

        if ($activeMembership !== null) {
            return redirect()->route('circles.show', $circle);
        }

        $membership = $request->user()->memberships()->create([
            'group_id' => $circle->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        // notifikasi realtime ke anggota yang sedang membuka chat circle ini
        MemberJoined::dispatch($membership);

        return redirect()->route('circles.show', $circle);
    }
}
