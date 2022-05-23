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
        Schema::table('okapi_types', static function (Blueprint $table) {
            $table->boolean('ownable')->default(false);
            $table->boolean('private')->default(false);
        });

        Schema::table('okapi_instances', static function (Blueprint $table) {
            $table->foreignId('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('okapi_types', static function (Blueprint $table) {
            $table->dropColumn('ownable', 'private');
        });

        Schema::table('okapi_instances', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
