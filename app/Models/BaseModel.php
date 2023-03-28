<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    public $incrementing = true;
    public $timestamps = false;
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $perPage = 20;
    protected $guarded = [];
}
