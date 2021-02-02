<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WirecardApp extends Model
{
    protected $table = 'wirecard_app';
    protected $fillable = ['data', 'type'];

    public $rules = [
        'data' => 'required',
        'type' => 'required'
    ];
}
