<?php

namespace Database\Factories;

use App\Models\PreventiveMaintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PreventiveMaintenance>
 */
class PreventiveMaintenanceFactory extends Factory
{
    protected $model = PreventiveMaintenance::class;

    public function definition(): array
    {
        return [
            'order' => $this->faker->numerify('PM###'),
            'notification_number' => $this->faker->numerify('NOTIF###'),
            'main_workctr' => $this->faker->bothify('WC-??-###'),
            'description' => $this->faker->sentence(8),
            'system_status' => $this->faker->randomElement(['OPEN', 'IN_PROGRESS', 'CLOSED']),

            'basic_start_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),

            'plan_total_cost' => $this->faker->numberBetween(1000, 5000).' USD',
            'actual_total_cost' => $this->faker->numberBetween(1000, 5000).' USD',

            // kosong (null) untuk kolom ini
            'actual_start_date' => null,
            'actual_start_time' => null,

            'actual_finish' => $this->faker->dateTimeBetween('-1 week', 'now'),

            // kosongkan juga entered_by
            'entered_by' => null,
            'order_type' => $this->faker->randomElement(['PM01', 'PM02', 'PM03']),
            'user_status' => $this->faker->randomElement(['CREATED', 'RELEASED', 'COMPLETE']),

            'functional_location' => $this->faker->bothify('FL-??-###'),
            'fl_desc' => $this->faker->sentence(6),

            'equipment' => $this->faker->bothify('EQ-??-###'),
            'eq_desc' => $this->faker->sentence(6),

            'tech_obj_desc' => $this->faker->sentence(5),
            'maintenance_plan' => $this->faker->bothify('PLAN-###'),
        ];
    }
}
