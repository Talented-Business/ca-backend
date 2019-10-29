<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Company;
use Faker\Generator as Faker;

$factory->define(Company::class, function (Faker $faker) {
    return [
        'name'=>$faker->firstName,
        'website'=>$faker->lastName,
        'state_incoporation'=>$faker->unique()->ean8,
        'entity_type'=>"Male",
        'industry'=>$faker->date("Y-m-d",'2000-01-01'),
        'size'=>$faker->country,
        'headquaters_addresses'=>$faker->phoneNumber,
        'legal_address'=>$faker->phoneNumber,
        'billing_address'=>$faker->unique()->safeEmail,
        'document_agreement'=>'Single',
        'document_signed_by'=>'a',
        'document_signature_date'=>$faker->date("Y-m-d",'2000-01-01'),
        'bank_name'=>$faker->state,
        'bank_account_name'=>$faker->streetAddress,
        'bank_account_number'=>1,
    ];
});
