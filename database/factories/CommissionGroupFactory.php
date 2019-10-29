<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\CommissionGroup;
use Faker\Generator as Faker;

$factory->define(CommissionGroup::class, function (Faker $faker) {
    return [
        'member_id'=>1,
    ];
});
