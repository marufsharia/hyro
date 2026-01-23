<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Marufsharia\Hyro\Support\Traits\HasUuidConfiguration;

return new class extends Migration
{
    use HasUuidConfiguration;

    public function up(): void
    {
        $tableName = Config::get('hyro.database.tables.privileges', 'hyro_privileges');

        Schema::create($tableName, function (Blueprint $table) {
            $this->addPrimaryKey($table);

            $table->string('slug', 255)->unique(); // Longer for dot notation
            $table->string('name', 191);
            $table->text('description')->nullable();

            // Wildcard support
            $table->boolean('is_wildcard')->default(false);
            $table->string('wildcard_pattern', 255)->nullable()->index();

            // Category for organization
            $table->string('category', 100)->nullable()->index();

            // Priority for resolution order
            $table->unsignedTinyInteger('priority')->default(50);

            // System protection
            $table->boolean('is_protected')->default(false);

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['slug', 'deleted_at'], 'hyro_privileges_slug_status_idx');
            $table->index(['is_wildcard', 'wildcard_pattern', 'deleted_at'], 'hyro_privileges_wildcard_idx');
            $table->index(['category', 'priority', 'deleted_at'], 'hyro_privileges_category_priority_idx');
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE {$tableName} COMMENT = 'Stores privilege definitions with wildcard pattern matching support'"
            );
        }

        if ($driver === 'pgsql') {
            DB::statement(
                "COMMENT ON TABLE {$tableName} IS 'Stores privilege definitions with wildcard pattern matching support'"
            );
        }


    }

    public function down(): void
    {
        Schema::dropIfExists(Config::get('hyro.database.tables.privileges', 'hyro_privileges'));
    }
};
