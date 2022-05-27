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
        Schema::create('okapi_relationships', static function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedInteger('api_visibility');
            $table->foreignId('okapi_type_from_id')
                ->references('id')
                ->on('okapi_types')
                ->cascadeOnDelete();
            $table->foreignId('okapi_type_to_id')
                ->references('id')
                ->on('okapi_types')
                ->cascadeOnDelete();
            $table->foreignId('okapi_field_display_id')->nullable()
                ->references('id')
                ->on('okapi_fields')
                ->nullOnDelete();
            $table->foreignId('reverse_okapi_field_display_id')->nullable()
                ->references('id')
                ->on('okapi_fields')
                ->nullOnDelete();
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
        Schema::dropIfExists('okapi_relationships');
    }
};
