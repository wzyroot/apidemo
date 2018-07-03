<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Admin::class, function (Faker\Generator $faker) { 
    static $password; 
 
    return [ 
        'name' => $faker->name, 
        'password' => $password ?: $password = bcrypt('123456'), 
        'email' =>  $faker->email, 
        'remember_token' => str_random(10), 
    ]; 
});
