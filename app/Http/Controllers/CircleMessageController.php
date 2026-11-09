<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\ChatGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CircleMessageController extends Controller
{
    /**
     * F4: kirim pesan — hanya anggota aktif.
     */
    public function store(StoreMessageRequest $request, ChatGroup $circle): RedirectResponse
    {
        if (! Gate::allows('sendMessage', $circle)) {
            abort(404);
        }

        $circle->messages()->create([
            'sender_id' => $request->user()->id,
            'message' => $request->input('message'),
        ]);

        return redirect()->route('circles.show', ['circle' => $circle->id, 'page' => $request->input('page')]);
    }
}
