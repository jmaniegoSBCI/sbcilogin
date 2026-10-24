<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

if (config('database.default') === 'mongodb') {
    abstract class DatabaseModel extends Model {}
} else {
    abstract class DatabaseModel extends \Illuminate\Database\Eloquent\Model {}
}
