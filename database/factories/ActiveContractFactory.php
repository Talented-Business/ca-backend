<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ActiveContract;
use Faker\Generator as Faker;

$factory->define(ActiveContract::class, function (Faker $faker) {
    return [
        'contract_id'=>1,
        'employee_id'=>1,
        'company_id'=>1,
    ];
});
