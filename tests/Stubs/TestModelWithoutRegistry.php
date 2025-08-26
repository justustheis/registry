<?php

namespace JustusTheis\Registry\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;

class TestModelWithoutRegistry extends Model
{
    protected $table = 'test_models_without_registry';

    protected $fillable = ['name'];

    public $timestamps = false;
}
