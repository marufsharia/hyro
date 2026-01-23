<?php

namespace Marufsharia\Hyro\Support\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

trait HasUuidConfiguration
{
    /**
     * Add primary key column based on configuration.
     */
    protected function addPrimaryKey(Blueprint $table): void
    {
        if (Config::get('hyro.uuid.enabled', false)) {
            $table->uuid('id')->primary();
            $table->uuid(Config::get('hyro.uuid.column', 'uuid'))->unique()->nullable();
        } else {
            $table->id();
        }
    }

    /**
     * Add foreign key reference with proper type.
     */
    protected function addForeignKey(Blueprint $table, string $column, string $references, string $on): void
    {
        if (Config::get('hyro.uuid.enabled', false)) {
            $table->uuid($column);
        } else {
            $table->unsignedBigInteger($column);
        }

        if (Config::get('hyro.database.foreign_keys', true)) {
            $table->foreign($column)
                ->references($references)
                ->on($on)
                ->onDelete('cascade');
        }
    }

    /**
     * Add user foreign key with configurable table name.
     */
    protected function addUserForeignKey(Blueprint $table, string $column = 'user_id'): void
    {
        if (Config::get('hyro.database.user_key_type', 'id') === 'uuid') {
            $table->uuid($column);
        } else {
            $table->unsignedBigInteger($column);
        }

        if (Config::get('hyro.database.foreign_keys', true)) {
            $table->foreign($column)
                ->references('id')
                ->on(Config::get('hyro.database.tables.users', 'users'))
                ->onDelete('cascade');
        }
    }
}
