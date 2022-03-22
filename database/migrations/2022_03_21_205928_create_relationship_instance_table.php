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
        Schema::create('okapi_relationship_instance', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('okapi_relationship_id');
            $table->foreignId('okapi_from_instance_id');
            $table->foreignId('okapi_to_instance_id');
            $table->timestamps();

            $table->foreign('okapi_relationship_id')
                ->references('id')
                ->on('okapi_relationships')
                ->cascadeOnDelete();
            $table->foreign('okapi_from_instance_id')
                ->references('id')
                ->on('okapi_instances')
                ->cascadeOnDelete();
            $table->foreign('okapi_to_instance_id')
                ->references('id')
                ->on('okapi_instances')
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
        Schema::dropIfExists('okapi_relationship_instance');
    }
};
