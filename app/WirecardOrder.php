<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WirecardOrder extends Model
{
    protected $table = 'wirecard_order';
    protected $fillable = ['order_wirecard_id', 'order_id'];
    public $timestamps = false;
}
