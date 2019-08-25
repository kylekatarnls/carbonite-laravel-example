<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    $user = Auth::user();
    $locals = [
        'valid_until' => '',
    ];

    if ($user) {
        app()->setLocale($user->language);

        if ($user->valid_until) {
            $locals['valid_until'] = $user->valid_until->tz($user->timezone)->calendar();
        }
    }

    return view('welcome', $locals);
});
