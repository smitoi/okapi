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
        Schema::create('okapi_rules', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('properties');
            $table->foreignId('okapi_field_id');
            $table->timestamps();

            $table->foreign('okapi_field_id')->references('id')->on('okapi_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('okapi_rules');
    }
};