<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->unique()->words(3, true),
            'enabled' => true,
            'type' => 'info',
            'message' => $this->faker->sentence(),
            'dismissible' => true,
            'dismiss_on_action' => false,
            'placements' => [Alert::PLACEMENT_DASHBOARD, Alert::PLACEMENT_SERVER],
            'audience' => 'everyone',
            'priority' => 0,
        ];
    }
}
