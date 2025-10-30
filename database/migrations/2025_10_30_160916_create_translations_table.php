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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale', 10);
            $table->text('value');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->unique(['key', 'locale']);
            $table->index(['locale']);
            $table->index(['key']);
            // Fulltext index to accelerate content search on MySQL 5.7+/InnoDB
            $table->fullText('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
