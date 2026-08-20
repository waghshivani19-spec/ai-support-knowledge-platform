<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('knowledge_base_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');

            $table->string('original_filename');

            $table->string('file_path');

            $table->string('mime_type')
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();

            $table->char('file_hash', 64)
                ->nullable();

            $table->enum('source_type', [
                'upload',
                'url',
                'text',
                'faq',
            ])->default('upload');

            $table->text('source_url')
                ->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'processed',
                'failed',
            ])->default('pending');

            $table->text('processing_error')
                ->nullable();

            $table->unsignedInteger('chunk_count')
                ->default(0);

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'knowledge_base_id',
                'status'
            ]);

            $table->index('file_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_documents');
    }
};