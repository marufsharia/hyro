<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hyro_plugin_versions', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id')->index();
            $table->string('version', 50);
            $table->date('release_date')->nullable();
            $table->text('changelog')->nullable();
            $table->boolean('breaking_changes')->default(false);
            $table->boolean('security_patch')->default(false);
            $table->string('download_url', 500)->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            
            $table->index(['plugin_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hyro_plugin_versions');
    }
};
