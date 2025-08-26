<?php

namespace JustusTheis\Registry\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use JustusTheis\Registry\Traits\HasRegistry;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestModelWithRegistry extends Model
{
    use HasRegistry, SoftDeletes;

    protected $table = 'test_models';

    protected $fillable = ['name'];

    public $timestamps = false;
}
