<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName       = Config::get('hyro.database.tables.privilege_role', 'hyro_privilege_role');
        $privilegesTable = Config::get('hyro.database.tables.privileges', 'hyro_privileges');
        $rolesTable      = Config::get('hyro.database.tables.roles', 'hyro_roles');
        $usersTable      = Config::get('hyro.database.tables.users', 'users');

        Schema::create($tableName, function (Blueprint $table) use ($privilegesTable, $rolesTable, $usersTable) {

            /*
             |--------------------------------------------------------------------------
             | Foreign Keys (UUID / BIGINT)
             |--------------------------------------------------------------------------
             */
            if (Config::get('hyro.uuid.enabled', false)) {
                $table->uuid('privilege_id');
                $table->uuid('role_id');
                $table->uuid('granted_by')->nullable();
            } else {
                $table->unsignedBigInteger('privilege_id');
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('granted_by')->nullable();
            }

            /*
             |--------------------------------------------------------------------------
             | Grant Metadata
             |--------------------------------------------------------------------------
             */
            $table->timestamp('granted_at')->useCurrent();
            $table->text('grant_reason')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Conditional Grants
             |--------------------------------------------------------------------------
             */
            $table->json('conditions')->nullable(); // Dynamic rule conditions
            $table->timestamp('expires_at')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Timestamps
             |--------------------------------------------------------------------------
             */
            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Composite Primary Key
             |--------------------------------------------------------------------------
             */
            $table->primary(['privilege_id', 'role_id'], 'hyro_privilege_role_pk');

            /*
             |--------------------------------------------------------------------------
             | Foreign Key Constraints (Optional)
             |--------------------------------------------------------------------------
             */
            if (Config::get('hyro.database.foreign_keys', true)) {

                $table->foreign('privilege_id')
                    ->references('id')
                    ->on($privilegesTable)
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($rolesTable)
                    ->onDelete('cascade');

                $table->foreign('granted_by')
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('set null');
            }

            /*
             |--------------------------------------------------------------------------
             | Performance Indexes (DB-agnostic)
             |--------------------------------------------------------------------------
             */
            $table->index(
                ['role_id', 'expires_at'],
                'hyro_privilege_role_role_active_idx'
            );

            $table->index(
                ['privilege_id', 'expires_at'],
                'hyro_privilege_role_privilege_active_idx'
            );
        });

        /*
         |--------------------------------------------------------------------------
         | Database-Specific Enhancements
         |--------------------------------------------------------------------------
         */
        $driver = DB::getDriverName();

        /*
         | PostgreSQL: GIN index for JSONB conditions
         */
        if ($driver === 'pgsql') {
            DB::statement(
                "CREATE INDEX hyro_privilege_role_conditions_gin
                 ON {$tableName}
                 USING GIN (conditions)"
            );

            DB::statement(
                "COMMENT ON TABLE {$tableName}
                 IS 'Many-to-many relationship between roles and privileges with conditional grants'"
            );
        }

        /*
         | MySQL: Table comment (JSON indexing intentionally skipped)
         | MySQL has no GIN index. JSON indexing must be done via generated columns
         | and is intentionally omitted for portability.
         */
        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE {$tableName}
                 COMMENT = 'Many-to-many relationship between roles and privileges with conditional grants'"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            Config::get('hyro.database.tables.privilege_role', 'hyro_privilege_role')
        );
    }
};
