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
        Schema::create('chat_history_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('chat_history_id')->constrained('chat_histories')->cascadeOnDelete();
            $table->string('message');
            $table->enum('status', ['in_progress', 'done'])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_history_steps');
    }
};
