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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("product_name");
            $table->string("product_image");
            $table->unsignedBigInteger("product_state_id");
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign(["product_state_id"], "fk_product_state_id")->references("id")->on("states")->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign("fk_product_state_id");
        });

        Schema::dropIfExists('products');
    }
};
