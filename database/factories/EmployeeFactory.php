<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Employee;
use Faker\Generator as Faker;

$factory->define(Employee::class, function (Faker $faker) {
    return [
        'first_name'=>$faker->firstName,
        'last_name'=>$faker->lastName,
        'id_number'=>$faker->unique()->ean8,
        'gender'=>"Male",
        'birthday'=>$faker->date("Y-m-d",'2000-01-01'),
        'nationality'=>$faker->country,
        'home_phone_number'=>$faker->phoneNumber,
        'mobile_phone_number'=>$faker->phoneNumber,
        'personal_email'=>$faker->unique()->safeEmail,
        'marital'=>'Single',
        'skype_id'=>'a',
        'country'=>$faker->country,
        'state'=>$faker->state,
        'home_address'=>$faker->streetAddress,
        'deport_america'=>1,
        'check_america'=>1,
        'check_background'=>1,
        'english_level'=>'Fluent',
        'available_works'=>'40',
        'have_computer'=>1,
        'have_monitor'=>1,
        'have_headset'=>1,
        'have_ethernet'=>1,
        'status'=>'hired',
    ];
});
