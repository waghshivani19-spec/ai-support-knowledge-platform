<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_knowledge_base(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson('/api/knowledge-bases', [
                'name' => 'Laravel Knowledge',
                'description' => 'Laravel documentation',
                'chunk_size' => 1000,
                'chunk_overlap' => 200,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'success',
                true
            );

        $this->assertDatabaseHas(
            'knowledge_bases',
            [
                'name' => 'Laravel Knowledge',
                'created_by' => $admin->id,
            ]
        );
    }

    public function test_customer_cannot_create_knowledge_base(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this
            ->actingAs($customer)
            ->postJson('/api/knowledge-bases', [
                'name' => 'Customer KB',
            ]);

        $response->assertForbidden();
    }
}