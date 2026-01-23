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
        $tableName = Config::get('hyro.database.tables.audit_logs', 'hyro_audit_logs');
        $driver    = DB::getDriverName();

        Schema::create($tableName, function (Blueprint $table) use ($driver) {

            /*
             |--------------------------------------------------------------------------
             | Primary Identifier
             |--------------------------------------------------------------------------
             */
            $table->unsignedBigInteger('id')->autoIncrement();

            /*
             |--------------------------------------------------------------------------
             | Polymorphic Subject
             |--------------------------------------------------------------------------
             */
            $table->string('auditable_type')->nullable()->index();
            $table->string('auditable_id')->nullable()->index();

            /*
             |--------------------------------------------------------------------------
             | Actor
             |--------------------------------------------------------------------------
             */
            if (Config::get('hyro.database.user_key_type', 'id') === 'uuid') {
                $table->uuid('user_id')->nullable();
            } else {
                $table->unsignedBigInteger('user_id')->nullable();
            }

            /*
             |--------------------------------------------------------------------------
             | Action
             |--------------------------------------------------------------------------
             */
            $table->string('event', 100)->index();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Change Tracking
             |--------------------------------------------------------------------------
             */
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            /*
             |--------------------------------------------------------------------------
             | Request Context
             |--------------------------------------------------------------------------
             */
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 2000)->nullable();
            $table->string('method', 10)->nullable();

            /*
             |--------------------------------------------------------------------------
             | Metadata
             |--------------------------------------------------------------------------
             */
            $table->json('tags')->nullable();
            $table->text('description')->nullable();
            $table->string('batch_uuid', 36)->nullable()->index();

            /*
             |--------------------------------------------------------------------------
             | Timestamps
             |--------------------------------------------------------------------------
             */
            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | MySQL Generated Columns (required for partitioning)
             |--------------------------------------------------------------------------
             */
            if ($driver === 'mysql') {
                $table->unsignedSmallInteger('audit_year')
                    ->storedAs('YEAR(created_at)');

                $table->date('logged_date')
                    ->storedAs('DATE(created_at)')
                    ->index();
            }

            /*
             |--------------------------------------------------------------------------
             | PRIMARY KEY (MUST include partition column)
             |--------------------------------------------------------------------------
             */
            if ($driver === 'mysql') {
                $table->primary(['id', 'audit_year'], 'hyro_audit_pk');
            } else {
                $table->primary('id');
            }

            /*
             |--------------------------------------------------------------------------
             | Indexes
             |--------------------------------------------------------------------------
             */
            $table->index(
                ['auditable_type', 'auditable_id', 'created_at'],
                'hyro_audit_auditable_timeline_idx'
            );

            $table->index(
                ['user_id', 'created_at'],
                'hyro_audit_user_timeline_idx'
            );

            $table->index(
                ['event', 'created_at'],
                'hyro_audit_event_timeline_idx'
            );

            $table->index(
                ['batch_uuid', 'created_at'],
                'hyro_audit_batch_idx'
            );

            $table->index(['created_at'], 'hyro_audit_retention_idx');

            /*
             |--------------------------------------------------------------------------
             | PostgreSQL JSON GIN Indexes
             |--------------------------------------------------------------------------
             */
            if ($driver === 'pgsql') {
                $table->rawIndex('((old_values)::jsonb)', 'hyro_audit_old_values_gin_idx');
                $table->rawIndex('((new_values)::jsonb)', 'hyro_audit_new_values_gin_idx');
                $table->rawIndex('((tags)::jsonb)', 'hyro_audit_tags_gin_idx');
            }
        });

        /*
         |--------------------------------------------------------------------------
         | MySQL Partitioning (NOW VALID)
         |--------------------------------------------------------------------------
         */
        if ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE {$tableName}
                PARTITION BY RANGE (audit_year) (
                    PARTITION p2024 VALUES LESS THAN (2025),
                    PARTITION p2025 VALUES LESS THAN (2026),
                    PARTITION p2026 VALUES LESS THAN (2027),
                    PARTITION p_future VALUES LESS THAN MAXVALUE
                )
            ");

            DB::statement(
                "ALTER TABLE {$tableName}
                 COMMENT = 'Partitioned audit log table with yearly retention and composite primary key'"
            );
        }

        /*
         |--------------------------------------------------------------------------
         | PostgreSQL Comment
         |--------------------------------------------------------------------------
         */
        if ($driver === 'pgsql') {
            DB::statement(
                "COMMENT ON TABLE {$tableName}
                 IS 'Audit logging with JSON tracking, polymorphic subjects, and timeline indexes'"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            Config::get('hyro.database.tables.audit_logs', 'hyro_audit_logs')
        );
    }
};
