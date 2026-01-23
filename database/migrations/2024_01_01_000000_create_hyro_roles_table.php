<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Marufsharia\Hyro\Support\Traits\HasUuidConfiguration;

return new class extends Migration
{
    use HasUuidConfiguration;

    public function up(): void
    {
        $tableName = Config::get('hyro.database.tables.roles', 'hyro_roles');

        Schema::create($tableName, function (Blueprint $table) {
            $this->addPrimaryKey($table);

            $table->string('slug', 191)->unique();
            $table->string('name', 191);
            $table->text('description')->nullable();

            // System protection flags
            $table->boolean('is_protected')->default(false);
            $table->boolean('is_system')->default(false);

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['slug', 'deleted_at'], 'hyro_roles_slug_status_idx');
            $table->index(['is_protected', 'deleted_at'], 'hyro_roles_protection_status_idx');
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE {$tableName} COMMENT = 'Stores role definitions with system protection flags'"
            );
        }

        if ($driver === 'pgsql') {
            DB::statement(
                "COMMENT ON TABLE {$tableName} IS 'Stores role definitions with system protection flags'"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('hyro.database.tables.roles', 'hyro_roles'));
    }
};
