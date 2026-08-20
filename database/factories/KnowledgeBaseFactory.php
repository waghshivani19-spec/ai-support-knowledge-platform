<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KnowledgeBaseFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'created_by' =>
                User::factory(),

            'name' =>
                $name,

            'slug' =>
                Str::slug($name),

            'description' =>
                fake()->optional()->paragraph(),

            'embedding_provider' =>
                'openai',

            'embedding_model' =>
                'text-embedding-3-small',

            'chunk_size' =>
                1000,

            'chunk_overlap' =>
                200,

            'is_active' =>
                true,
        ];
    }
}