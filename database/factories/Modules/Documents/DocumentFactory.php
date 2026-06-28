<?php

namespace Database\Factories\Modules\Documents;

use App\Modules\Documents\Models\Document;
use App\Modules\KnowledgeBases\Models\KnowledgeBase;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    private static array $departments = ['hr', 'legal', 'finance', 'engineering', 'marketing'];

    private static array $tagPool = ['policy', 'guide', 'procedure', 'report', 'onboarding', 'leave', 'benefits', 'security', 'compliance', 'training'];

    private static array $mimeTypes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function definition(): array
    {
        $department = fake()->randomElement(self::$departments);
        $tags = fake()->randomElements(self::$tagPool, fake()->numberBetween(1, 3));

        return [
            'knowledge_base_id' => KnowledgeBase::factory(),
            'uploaded_by'       => User::factory(),
            'title'             => fake()->realText(60),
            'content'           => fake()->realText(800),
            'file_name'         => fake()->slug(3) . '.pdf',
            'file_path'         => 'documents/' . fake()->uuid() . '.pdf',
            'mime_type'         => fake()->randomElement(self::$mimeTypes),
            'file_size_bytes'   => fake()->numberBetween(10_000, 5_000_000),
            'status'            => 'indexed',
            'metadata'          => [
                'department' => $department,
                'tags'       => $tags,
                'year'       => fake()->numberBetween(2020, 2026),
                'language'   => fake()->randomElement(['en', 'uk']),
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function forKnowledgeBase(KnowledgeBase $kb): static
    {
        return $this->state(['knowledge_base_id' => $kb->id]);
    }
}
