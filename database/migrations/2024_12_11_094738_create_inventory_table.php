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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string("size");
            $table->string("length");
            $table->string("quantity");
            $table->unsignedBigInteger("product_id");
            $table->timestamps();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign(["product_id"], "fk_product_id")->references("id")->on("products")->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign("fk_product_id");
        });

        Schema::dropIfExists('inventory');
    }
};
