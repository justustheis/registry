<?php

namespace JustusTheis\Registry\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Traits\HasRegistry;

class TestUser extends Model
{
    use HasRegistry;

    protected $table = 'users';

    protected $fillable = ['name'];

    public $timestamps = false;

    protected $attributes = [
        'id'   => 1,
        'name' => 'Test User',
    ];
}
