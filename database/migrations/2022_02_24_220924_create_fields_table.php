<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('okapi_fields', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->boolean('dashboard_visible')->default(false);
            $table->boolean('api_visible')->default(false);
            $table->json('properties');
            $table->foreignId('okapi_type_id')
                ->references('id')
                ->on('okapi_types')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('okapi_fields');
    }
};
