<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_group', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 100);
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('chat_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['admin', 'member']);
            $table->timestamp('left_at')->nullable(); // NULL = anggota aktif
            $table->timestamp('joined_at');

            $table->foreign('group_id')->references('id')->on('chat_group')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['group_id', 'left_at']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('sender_id');
            $table->string('message', 2000);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('group_id')->references('id')->on('chat_group')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users');

            $table->index(['group_id', 'created_at']);
        });

        Schema::create('chat_group_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('created_by');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at'); // diisi 7 hari sejak dibuat oleh aplikasi
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('group_id')->references('id')->on('chat_group')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_group_invitations');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_group_members');
        Schema::dropIfExists('chat_group');
    }
};
