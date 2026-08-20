<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->string('embedding_provider')
                ->default('openai');

            $table->string('embedding_model')
                ->nullable();

            $table->unsignedInteger('chunk_size')
                ->default(1000);

            $table->unsignedInteger('chunk_overlap')
                ->default(200);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index(['created_by', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};