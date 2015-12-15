<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'surname' => $faker->lastName,
        'forenames' => $faker->firstName(),
        'email' => $faker->email,
        'is_student' => $faker->boolean(95),
        'last_login' => $faker->dateTimeThisYear(),
        'remember_token' => str_random(10),
    ];
});
$factory->define(App\Role::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->name,
        'label' => $faker->sentence(3),
    ];
});
$factory->define(App\Permission::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->name,
        'label' => $faker->sentence(3),
    ];
});
$factory->define(App\Project::class, function (Faker\Generator $faker) {
    return [
        'title' => implode(' ', $faker->words(3)),
        'description' => $faker->paragraph(3),
        'maximum_students' => $faker->numberBetween(1, 5),
        'is_active' => $faker->boolean(90),
        'type_id' => $faker->numberBetween(1, 2),
        'user_id' => $faker->numberBetween(1, 3),
    ];
});
$factory->define(App\Course::class, function (Faker\Generator $faker) {
    return [
        'title' => implode(" ", $faker->words(3)),
        'code' => 'ENG' . $faker->numberBetween(1000, 9999),
    ];
});
$factory->define(App\ProjectType::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word . $faker->word . $faker->word,
    ];
});
