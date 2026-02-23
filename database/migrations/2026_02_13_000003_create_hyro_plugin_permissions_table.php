<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hyro_plugin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id')->index();
            $table->string('permission_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['plugin_id', 'permission_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hyro_plugin_permissions');
    }
};
