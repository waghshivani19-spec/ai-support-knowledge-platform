<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('sender_type', [
                'customer',
                'agent',
                'ai',
                'system',
            ]);

            $table->longText('content');

            $table->json('metadata')
                ->nullable();

            $table->unsignedInteger('input_tokens')
                ->nullable();

            $table->unsignedInteger('output_tokens')
                ->nullable();

            $table->unsignedInteger('response_time_ms')
                ->nullable();

            $table->timestamps();

            $table->index([
                'conversation_id',
                'created_at'
            ]);

            $table->index([
                'sender_type',
                'created_at'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};