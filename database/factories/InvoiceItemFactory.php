<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\InvoiceItem;
use Faker\Generator as Faker;

$factory->define(InvoiceItem::class, function (Faker $faker) {
    return [
        'invoice_id'=>3,
        'employee_id'=>2,
        'slug'=>$faker->lastName,
        'task'=>$faker->word,
        'description'=>$faker->name,
        'rate'=>3,
        'amount'=>12,
        'total'=>34,
    ];
});
