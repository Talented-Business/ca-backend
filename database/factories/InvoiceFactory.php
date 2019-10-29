<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'company_id' => 1,
        'invoicing_date' => "2019-08-01",
        'start_date' => "2019-08-01",
        'end_date' => "2019-08-08",
        'total' => 1892,
        'status' => 'Proforma',
    ];
});
