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
        'forenames' => $faker->firstName,
        'email' => $faker->email,
        'is_student' => false,
        'last_login' => $faker->dateTimeThisYear(),
        'is_admin' => false,
        'remember_token' => str_random(10),
    ];
});
$factory->state(App\User::class, 'admin', function ($faker) {
    return [
        'is_admin' => true,
        'is_student' => false,
    ];
});
$factory->state(App\User::class, 'convenor', function ($faker) {
    return [
        'is_convenor' => true,
        'is_student' => false,
    ];
});
$factory->state(App\User::class, 'student', function ($faker) {
    return [
        'is_student' => true,
        'username' => $faker->numberBetween(1000000, 9999999) . $faker->randomLetter,
        'bio' => $faker->paragraph,
    ];
});
$factory->state(App\User::class, 'staff', function ($faker) {
    return [
        'is_student' => false,
    ];
});

$factory->define(App\Project::class, function (Faker\Generator $faker) {
    return [
        'title' => implode(' ', $faker->words(3)),
        'description' => $faker->paragraph(3),
        'maximum_students' => $faker->numberBetween(3, 10),
        'is_active' => true,
        'discipline_id' => null,
        'user_id' => function () {
            return factory(App\User::class)->states('staff')->create()->id;
        },
    ];
});

$factory->define(App\ProjectFile::class, function (Faker\Generator $faker) {
    return [
        'filename' => $faker->word . '.' . $faker->fileExtension,
        'original_filename' => $faker->word . '.' . $faker->fileExtension,
        'file_size' => $faker->numberBetween(1000, 10000),
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        },
    ];
});

$factory->define(App\ProjectLink::class, function (Faker\Generator $faker) {
    return [
        'url' => $faker->url,
        'project_id' => function () {
            return factory(App\Project::class)->create()->id;
        },
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
$factory->define(App\PasswordReset::class, function (Faker\Generator $faker) {
    return [
        'user_id' => $faker->randomNumber(),
        'token' => strtolower(str_random(32)),
    ];
});

$factory->define(App\Discipline::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word,
    ];
});
