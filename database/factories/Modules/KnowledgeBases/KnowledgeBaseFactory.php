<?php

namespace Database\Factories\Modules\KnowledgeBases;

use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBase>
 */
class KnowledgeBaseFactory extends Factory
{
    protected $model = KnowledgeBase::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'      => $name,
            'slug'      => Str::slug($name),
            'is_public' => false,
            'owner_id'  => User::factory(),
        ];
    }
}
