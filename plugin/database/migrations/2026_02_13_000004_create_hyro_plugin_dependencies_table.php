<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hyro_plugin_dependencies', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id')->index();
            $table->string('depends_on');
            $table->enum('type', ['required', 'optional', 'conflicts'])->default('required');
            $table->string('version_constraint', 50)->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps();
            
            $table->unique(['plugin_id', 'depends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hyro_plugin_dependencies');
    }
};
