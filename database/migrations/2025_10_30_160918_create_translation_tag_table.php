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
        Schema::create('translation_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('translation_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->primary(['translation_id', 'tag_id']);
            $table->index('tag_id');
            $table->foreign('translation_id')->references('id')->on('translations')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_tag');
    }
};
