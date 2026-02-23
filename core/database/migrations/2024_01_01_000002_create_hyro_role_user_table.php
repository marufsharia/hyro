<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = Config::get('hyro.database.tables.role_user', 'hyro_role_user');
        $rolesTable = Config::get('hyro.database.tables.roles', 'hyro_roles');
        $usersTable = Config::get('hyro.database.tables.users', 'users');

        Schema::create($tableName, function (Blueprint $table) use ($rolesTable, $usersTable) {
            // Composite primary key for uniqueness
            if (Config::get('hyro.uuid.enabled', false)) {
                $table->uuid('role_id');
                Config::get('hyro.database.user_key_type', 'id') === 'uuid'
                    ? $table->uuid('user_id')
                    : $table->unsignedBigInteger('user_id');
            } else {
                $table->unsignedBigInteger('role_id');
                Config::get('hyro.database.user_key_type', 'id') === 'uuid'
                    ? $table->uuid('user_id')
                    : $table->unsignedBigInteger('user_id');
            }

            // Assignment metadata
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('assignment_reason')->nullable();

            // Expiration support
            $table->timestamp('expires_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Composite primary key
            $table->primary(['role_id', 'user_id']);

            // Foreign key constraints (conditional)
            if (Config::get('hyro.database.foreign_keys', true)) {
                // Role foreign key
                $table->foreign('role_id')
                    ->references('id')
                    ->on($rolesTable)
                    ->onDelete('cascade');

                // User foreign key
                $table->foreign('user_id')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('cascade');

                // Assigner foreign key
                $table->foreign('assigned_by')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('set null');
            }

            // Performance indexes
            $table->index(['user_id', 'expires_at'], 'hyro_role_user_active_idx');
            $table->index(['role_id', 'expires_at'], 'hyro_role_user_role_active_idx');
            $table->index(['assigned_by', 'assigned_at'], 'hyro_role_user_assigner_idx');
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE {$tableName} COMMENT = 'Many-to-many relationship between users and roles with expiration support'"
            );
        }

        if ($driver === 'pgsql') {
            DB::statement(
                "COMMENT ON TABLE {$tableName} IS 'Many-to-many relationship between users and roles with expiration support'"
            );
        }

    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('hyro.database.tables.role_user', 'hyro_role_user'));
    }
};
