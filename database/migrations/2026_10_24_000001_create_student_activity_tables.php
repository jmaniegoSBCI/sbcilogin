<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mongodb') {
            Schema::create('activity_logs', function (Blueprint $collection) {
                $collection->index(['user_role' => 1, 'occurred_at' => -1]);
                $collection->index('occurred_at');
            });

            Schema::create('active_sessions', function (Blueprint $collection) {
                $collection->unique('session_id');
                $collection->index('user_id');
                $collection->index('user_role');
                $collection->index('last_activity_at');
                $collection->index('ended_at');
            });

            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id', 36)->index();
            $table->string('user_email');
            $table->string('user_name');
            $table->string('user_role', 20)->index();
            $table->string('event', 20);
            $table->timestamp('occurred_at')->index();
            $table->string('ip_address', 45)->nullable();
        });

        Schema::create('active_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->unique();
            $table->string('user_id', 36)->index();
            $table->string('user_email');
            $table->string('user_name');
            $table->string('user_role', 20)->index();
            $table->timestamp('logged_in_at');
            $table->timestamp('last_activity_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_sessions');
        Schema::dropIfExists('activity_logs');
    }
};
