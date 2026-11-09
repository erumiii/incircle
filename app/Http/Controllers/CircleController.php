<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCircleRequest;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CircleController extends Controller
{
    /**
     * F8: daftar circle yang diikuti pengguna.
     */
    public function index(Request $request): View
    {
        $circles = $request->user()
            ->activeMemberships()
            ->with('group')
            ->orderByDesc('joined_at')
            ->get();

        return view('circles.index', ['memberships' => $circles]);
    }

    public function create(): View
    {
        return view('circles.create');
    }

    /**
     * F2: buat circle; pembuat otomatis ADMIN.
     */
    public function store(StoreCircleRequest $request): RedirectResponse
    {
        $group = ChatGroup::create($request->validated());

        ChatGroupMember::create([
            'group_id' => $group->id,
            'user_id' => $request->user()->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return redirect()->route('circles.show', $group);
    }

    /**
     * F4: halaman circle — non-anggota menerima 404 agar circle tidak bocor.
     */
    public function show(Request $request, ChatGroup $circle): View|RedirectResponse
    {
        if (! Gate::allows('view', $circle)) {
            abort(404);
        }

        $messages = $circle->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->paginate(50);

        $membership = $request->user()
            ->memberships()
            ->where('group_id', $circle->id)
            ->whereNull('left_at')
            ->first();

        return view('circles.show', [
            'circle' => $circle,
            'messages' => $messages,
            'role' => $membership->role,
        ]);
    }

    /**
     * F5: form edit identitas circle — hanya ADMIN.
     */
    public function edit(Request $request, ChatGroup $circle): View
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        return view('circles.edit', ['circle' => $circle]);
    }

    public function update(StoreCircleRequest $request, ChatGroup $circle): RedirectResponse
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        $circle->update($request->validated());

        return redirect()->route('circles.show', $circle)->with('status', 'circle-updated');
    }

    /**
     * F6: hapus circle — konfirmasi dengan mengetik nama circle.
     */
    public function destroy(Request $request, ChatGroup $circle): RedirectResponse
    {
        if (! Gate::allows('manage', $circle)) {
            abort(404);
        }

        $request->validate([
            'confirm_name' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($circle) {
                if (trim($value) !== $circle->group_name) {
                    $fail('Ketik nama circle persis untuk mengonfirmasi penghapusan.');
                }
            }],
        ]);

        $circle->delete(); // FK cascade menghapus anggota, pesan, dan undangan

        return redirect()->route('circles.index')->with('status', 'circle-deleted');
    }
}
