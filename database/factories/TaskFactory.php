<?php

namespace Database\Factories;

use App\Enums\Task\TaskStatus;
use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $creator = $this->getCreator();

        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(TaskStatus::values()),
            'completed_at' => null,
            'due_at' => now()->addDays(random_int(5, 20)),
            'creator_id' => $creator->id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Task $task) {

            // get random users for task
            $users = User::where('id', '!=', $task->creator_id)
                ->inRandomOrder()
                ->take(rand(1, 5))
                ->pluck('id')
                ->toArray();

            // add creator user to task
            $task->users()->syncWithoutDetaching([$task->creator_id]);

            // add random users to task
            $task->users()->syncWithoutDetaching($users);

        });
    }

    private function getCreator()
    {
        $user = User::inRandomOrder()->first();

        if ($user) {
            return $user;
        }

        return User::factory()->create();
    }
}
