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
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_chat_session_id')->constrained('user_chats', 'session_id')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('user_input')->nullable();
            $table->text('message')->nullable();
            $table->string('job_id')->nullable()->index();
            $table->enum('job_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
