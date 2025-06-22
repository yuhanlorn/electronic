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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_id')->nullable()->unique()->index();

            $table->foreignId('parent_id')->nullable()->references('id')->on('categories')->onDelete('cascade');
            $table->string('for')->default('product')->nullable();
            $table->string('type')->default('category')->nullable();
            $table->json('name');
            $table->string('slug')->unique()->index();
            $table->json('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            $table->boolean('is_active')->default(1)->nullable();
            $table->boolean('show_in_menu')->default(0)->nullable();

            $table->timestamps();

            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
