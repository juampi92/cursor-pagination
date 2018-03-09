<?php

namespace Juampi92\CursorPagination\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];

    protected $primaryKey = '_id';

    protected $casts = ['datetime' => 'datetime'];
}
