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
        Schema::create('bus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('thumb');
            $table->string('seat');
            $table->string('type');
            $table->unsignedBigInteger('categories_id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('vendor_id');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('bus');
    }
};
