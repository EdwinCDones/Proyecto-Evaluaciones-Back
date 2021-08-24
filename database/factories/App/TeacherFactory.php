<?php

namespace Database\Factories\App;

use App\Models\App\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition()
    {
        return [
            'state_id' => 1
        ];
    }
}
