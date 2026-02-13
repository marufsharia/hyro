<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hyro_plugin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_id')->index();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string');
            $table->timestamps();
            
            $table->unique(['plugin_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hyro_plugin_settings');
    }
};
