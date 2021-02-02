<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    protected $perPage = 50;

    protected $filters = [];

    public function ScopeActives($query)
    {
        return $query->whereStatus(1);
    }

    public function scopeSearch($query)
    {
        return $query->whereLike($this->filters, request()->search());
    }
}
