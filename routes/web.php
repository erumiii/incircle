<?php

use App\Http\Controllers\CircleController;
use App\Http\Controllers\CircleInvitationController;
use App\Http\Controllers\CircleMemberController;
use App\Http\Controllers\CircleMessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CircleController::class, 'index'])
    ->middleware(['auth'])
    ->name('circles.index');

Route::get('/circles/create', [CircleController::class, 'create'])
    ->middleware(['auth'])
    ->name('circles.create');

Route::post('/circles', [CircleController::class, 'store'])
    ->middleware(['auth'])
    ->name('circles.store');

Route::get('/circles/{circle}', [CircleController::class, 'show'])
    ->middleware(['auth'])
    ->name('circles.show');

Route::get('/circles/{circle}/edit', [CircleController::class, 'edit'])
    ->middleware(['auth'])
    ->name('circles.edit');

Route::patch('/circles/{circle}', [CircleController::class, 'update'])
    ->middleware(['auth'])
    ->name('circles.update');

Route::delete('/circles/{circle}', [CircleController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('circles.destroy');

Route::get('/circles/{circle}/members', [CircleMemberController::class, 'index'])
    ->middleware(['auth'])
    ->name('circles.members');

Route::delete('/circles/{circle}/members/{member}', [CircleMemberController::class, 'kick'])
    ->middleware(['auth'])
    ->name('circles.members.kick');

Route::get('/circles/{circle}/invitations', [CircleInvitationController::class, 'index'])
    ->middleware(['auth'])
    ->name('circles.invitations');

Route::post('/circles/{circle}/invitations', [CircleInvitationController::class, 'store'])
    ->middleware(['auth'])
    ->name('circles.invitations.store');

Route::delete('/circles/{circle}/invitations/{invitation}', [CircleInvitationController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('circles.invitations.revoke');

Route::post('/circles/{circle}/messages', [CircleMessageController::class, 'store'])
    ->middleware(['auth'])
    ->name('circles.messages.store');

Route::get('/join/{token}', [CircleInvitationController::class, 'showJoin'])
    ->middleware(['auth'])
    ->name('join.show');

Route::post('/join/{token}', [CircleInvitationController::class, 'join'])
    ->middleware(['auth'])
    ->name('join');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
