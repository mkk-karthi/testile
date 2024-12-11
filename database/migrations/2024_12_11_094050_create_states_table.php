<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string("state_name");
            $table->unsignedBigInteger("country_id");
        });

        Schema::table('states', function (Blueprint $table) {
            $table->foreign(["country_id"], "fk_country_id")->references("id")->on("countries")->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropForeign("fk_country_id");
        });

        Schema::dropIfExists('states');
    }
};
