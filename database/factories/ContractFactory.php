<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contract;
use Faker\Generator as Faker;

$factory->define(Contract::class, function (Faker $faker) {
    return [
        'employee_id'=>$faker->firstName,
        'company_id'=>$faker->lastName,
        'title'=>$faker->unique()->ean8,
        'start_date'=>'2019-01-01',
        'position'=>$faker->country,
        'department_id'=>1,
        'work_location'=>$faker->phoneNumber,
        'employment_type'=>$faker->unique()->safeEmail,
        'employment_status'=>'Single',
        'manager'=>'a',
        'worksnap_id'=>$faker->country,
        'pay_days'=>$faker->state,
        'deduction_item'=>$faker->streetAddress,
        'compensation'=>1,
        'hourly_rate'=>1,
        'hours_per_day_period'=>1,
    ];
});
