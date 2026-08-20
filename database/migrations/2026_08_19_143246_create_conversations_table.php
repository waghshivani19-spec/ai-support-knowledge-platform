<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('knowledge_base_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('session_id')
                ->unique();

            $table->string('title')
                ->nullable();

            $table->enum('status', [
                'open',
                'waiting',
                'human',
                'resolved',
                'closed',
            ])->default('open');

            $table->boolean('is_ai_enabled')
                ->default(true);

            $table->timestamp('last_message_at')
                ->nullable();

            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'knowledge_base_id',
                'status'
            ]);

            $table->index([
                'assigned_agent_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};