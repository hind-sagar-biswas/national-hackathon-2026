<?php

use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Middleware\AuthenticateSession;

$cfg = [

    /*
    |--------------------------------------------------------------------------
    | Jetstream Stack
    |--------------------------------------------------------------------------
    |
    | This configuration value informs Jetstream which "stack" you will be
    | using for your application. In general, this value is set for you
    | during installation and will not need to be changed after that.
    |
    */

    'stack' => 'inertia',

    /*
    |--------------------------------------------------------------------------
    | Jetstream Route Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Jetstream will assign to the routes
    | that it registers with the application. When necessary, you may modify
    | these middleware; however, this default value is usually sufficient.
    |
    */

    'middleware' => ['web'],

    'auth_session' => AuthenticateSession::class,

    /*
    |--------------------------------------------------------------------------
    | Jetstream Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify the authentication guard Jetstream will use while
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'sanctum',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of Jetstream's features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features or you can even remove all of these if you need to.
    |
    */

    'features' => [],

    /*
    |--------------------------------------------------------------------------
    | Profile Photo Disk
    |--------------------------------------------------------------------------
    |
    | This configuration value determines the default disk that will be used
    | when storing profile photos for your application's users. Typically
    | this will be the "public" disk but you may adjust this if needed.
    |
    */

    'profile_photo_disk' => 'public',

];

if (env('TERMS_AND_COND', false)) {
    $cfg['features'][] = Features::termsAndPrivacyPolicy();
}
if (env('PROFILE_PIC', false)) {
    $cfg['features'][] = Features::profilePhotos();
}
if (env('API_SUPPORT', false)) {
    $cfg['features'][] = Features::api();
}
if (env('TEAMS', false)) {
    $cfg['features'][] = Features::teams(['invitations' => true]);
}
if (env('ACC_DELETION', true)) {
    $cfg['features'][] = Features::accountDeletion();
}

return $cfg;
