<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Task>
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isDone = $this->faker->boolean(30);
        $due = $this->faker->optional()->dateTimeBetween('now', '+3 months');
        $doneAt = $isDone ? $this->faker->optional()->dateTimeBetween('-1 month', 'now') : null;

        return [
            'category_id' => $this->faker->optional()->randomElement([
                TaskCategory::factory(),
                null,
            ]),
            'title' => $this->faker->sentence(nbWords: 6),
            'text' => $this->faker->optional()->paragraphs(nb: 3, asText: true),
            'due_date' => $due,
            'done_at' => $doneAt,
        ];
    }
}
