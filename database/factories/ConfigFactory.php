<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Config;
use Faker\Generator as Faker;

$factory->define(Config::class, function (Faker $faker) {
    return [
        'name'=>'company_fee',
        'value'=>10
    ];
});
