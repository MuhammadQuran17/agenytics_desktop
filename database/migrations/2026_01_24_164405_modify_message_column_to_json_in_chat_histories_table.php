<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('chat_histories', function (Blueprint $table) {
                $table->json('message')->nullable()->change();
            });

            return;
        }

        Schema::table('chat_histories', function (Blueprint $table) {
            // Convert existing text to valid JSON by wrapping in quotes, then convert to JSON
            DB::statement('ALTER TABLE chat_histories ALTER COLUMN message TYPE json USING to_jsonb(message)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('chat_histories', function (Blueprint $table) {
                $table->text('message')->nullable()->change();
            });

            return;
        }

        Schema::table('chat_histories', function (Blueprint $table) {
            DB::statement('ALTER TABLE chat_histories ALTER COLUMN message TYPE text USING message::text');
        });
    }
};
