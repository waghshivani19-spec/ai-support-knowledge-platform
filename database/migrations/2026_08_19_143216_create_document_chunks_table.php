<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('knowledge_document_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('chunk_index');

            $table->longText('content');

            $table->unsignedInteger('token_count')
                ->nullable();

            $table->string('vector_id')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'knowledge_document_id',
                'chunk_index'
            ]);

            $table->index('vector_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};