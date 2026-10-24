<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User;

if (config('database.default') === 'mongodb') {
    abstract class AuthenticatableModel extends User {}
} else {
    abstract class AuthenticatableModel extends \Illuminate\Foundation\Auth\User {}
}
