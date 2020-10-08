<?php

return [
    'uog_required_choices' => 3,
    'uestc_required_choices' => 6,
    'single_uog_required_choices' => 0,
    'single_uestc_required_choices' => 9,
    'resetTokenExpires' => 7,
    'logins_allowed' => env('ALLOW_STUDENT_LOGINS', true),
    'maximumAllowedToApply' => 6,
    'uestc_unique_supervisors' => env('UESTC_UNIQUE_SUPERVISORS', true),
    'uog_unique_supervisors' => env('UOG_UNIQUE_SUPERVISORS', true),
];
