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
    public function up()
    {
        Schema::create('okapi_relationships', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->foreignId('okapi_type_from_id');
            $table->foreignId('okapi_type_to_id');
            $table->timestamps();

            $table->foreign('okapi_type_from_id')
                ->references('id')
                ->on('okapi_types')
                ->cascadeOnDelete();
            $table->foreign('okapi_type_to_id')
                ->references('id')
                ->on('okapi_types')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('okapi_relationships');
    }
};
