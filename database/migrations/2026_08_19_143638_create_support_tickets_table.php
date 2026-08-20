<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('ticket_number')
                ->unique();

            $table->string('subject');

            $table->text('description')
                ->nullable();

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent',
            ])->default('medium');

            $table->enum('status', [
                'open',
                'in_progress',
                'waiting_customer',
                'resolved',
                'closed',
            ])->default('open');

            $table->timestamp('resolved_at')
                ->nullable();

            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'status',
                'priority'
            ]);

            $table->index([
                'assigned_agent_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};