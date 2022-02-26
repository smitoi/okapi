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
        Schema::create('okapi_instance_field', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->foreignId('okapi_field_id');
            $table->foreignId('okapi_instance_id');
            $table->timestamps();

            $table->foreign('okapi_field_id')->references('id')->on('okapi_fields');
            $table->foreign('okapi_instance_id')->references('id')->on('okapi_instances');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('okapi_instance_field');
    }
};
