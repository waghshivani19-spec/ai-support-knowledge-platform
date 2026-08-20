<?php

namespace Tests\Feature;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class KnowledgeDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_upload_document(): void
    {
        Queue::fake();

        $agent = User::factory()->create([
            'role' => 'agent',
        ]);

        $knowledgeBase = KnowledgeBase::factory()->create([
            'created_by' => $agent->id,
        ]);

        $file = UploadedFile::fake()->create(
            'laravel.pdf',
            500,
            'application/pdf'
        );

        $response = $this
            ->actingAs($agent)
            ->postJson(
                "/api/v1/knowledge-bases/{$knowledgeBase->id}/documents",
                [
                    'file' => $file,
                    'title' => 'Laravel Documentation',
                ]
            );

        $response->assertCreated();

        $this->assertDatabaseHas(
            'knowledge_documents',
            [
                'knowledge_base_id' =>
                    $knowledgeBase->id,

                'title' =>
                    'Laravel Documentation',

                'status' =>
                    'pending',
            ]
        );
    }

    public function test_customer_cannot_upload_document(): void
    {
        Queue::fake();

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $knowledgeBase = KnowledgeBase::factory()->create();

        $file = UploadedFile::fake()->create(
            'test.pdf',
            500,
            'application/pdf'
        );

        $response = $this
            ->actingAs($customer)
            ->postJson(
                "/api/v1/knowledge-bases/{$knowledgeBase->id}/documents",
                [
                    'file' => $file,
                ]
            );

        $response->assertForbidden();
    }
}