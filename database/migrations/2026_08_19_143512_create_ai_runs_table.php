<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('message_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('provider')
                ->default('openai');

            $table->string('model');

            $table->string('operation')
                ->default('chat');

            $table->unsignedInteger('input_tokens')
                ->nullable();

            $table->unsignedInteger('output_tokens')
                ->nullable();

            $table->unsignedInteger('total_tokens')
                ->nullable();

            $table->unsignedInteger('retrieval_count')
                ->default(0);

            $table->unsignedInteger('latency_ms')
                ->nullable();

            $table->decimal('temperature', 4, 2)
                ->nullable();

            $table->decimal('estimated_cost', 12, 8)
                ->nullable();

            $table->enum('status', [
                'started',
                'completed',
                'failed',
            ])->default('started');

            $table->text('error_message')
                ->nullable();

            $table->json('metadata')
                ->nullable();

            $table->timestamps();

            $table->index([
                'provider',
                'model'
            ]);

            $table->index([
                'status',
                'created_at'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_runs');
    }
};