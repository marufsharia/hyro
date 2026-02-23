<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Config::get('hyro.database.tables.user_suspensions', 'hyro_user_suspensions');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();

            // User reference
            if (Config::get('hyro.database.user_key_type', 'id') === 'uuid') {
                $table->uuid('user_id');
            } else {
                $table->unsignedBigInteger('user_id');
            }

            // Suspension details
            $table->string('reason', 500);
            $table->text('details')->nullable();

            // Suspension period
            $table->timestamp('suspended_at')->useCurrent();
            $table->timestamp('suspended_until')->nullable(); // null = indefinite
            $table->timestamp('unsuspended_at')->nullable();

            // Suspender information
            $table->unsignedBigInteger('suspended_by')->nullable();
            $table->unsignedBigInteger('unsuspended_by')->nullable();

            // Security context
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Automatic vs manual suspension
            $table->boolean('is_automatic')->default(false);
            $table->string('auto_reason_code', 100)->nullable();

            // Foreign key constraints
            if (Config::get('hyro.database.foreign_keys', true)) {
                $usersTable = Config::get('hyro.database.tables.users', 'users');

                $table->foreign('user_id')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('cascade');

                $table->foreign('suspended_by')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('set null');

                $table->foreign('unsuspended_by')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('set null');
            }

            // Performance indexes
            $table->index(['user_id', 'suspended_until'], 'hyro_user_suspensions_active_idx');
            $table->index(['suspended_at', 'is_automatic'], 'hyro_user_suspensions_timestamp_idx');
            $table->index(['suspended_by', 'suspended_at'], 'hyro_user_suspensions_suspender_idx');
            $table->index(['auto_reason_code', 'suspended_at'], 'hyro_user_suspensions_auto_reason_idx');

            $table->timestamps();
        });


        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE {$tableName} COMMENT = 'Tracks users suspensions with automatic/manual tracking and indefinite/temporary periods'"
            );
        }

        if ($driver === 'pgsql') {
            DB::statement(
                "COMMENT ON TABLE {$tableName} IS 'Tracks users suspensions with automatic/manual tracking and indefinite/temporary periods'"
            );
        }

    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('hyro.database.tables.user_suspensions', 'hyro_user_suspensions'));
    }
};
