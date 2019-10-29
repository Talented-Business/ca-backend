<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Commission;
use Faker\Generator as Faker;

$factory->define(Commission::class, function (Faker $faker) {
    $numbers = ['06','07','08','09','10','11','12','13','14'];
    $number = $numbers[rand(0,8)];
    $date = "2019-10-$number 02:20:35";
    return [
        'group_id'=>1,
        'name'=>$faker->word,
        'fee'=>5,
        'quantity'=>10,
        'created_at'=>$date,
        'updated_at'=>$date,
    ];
});
