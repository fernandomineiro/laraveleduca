<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withTrashed()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyTrashed()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutTrashed()
 */
trait EducazSoftDelete {

    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = ['Restore', 'WithTrashed', 'WithoutTrashed', 'OnlyTrashed'];

    // adjust boot function
    public static function boot() {

        $softDeleteColumn = static::SOFT_DELETE ? static::SOFT_DELETE : 'status';

        // run parent
        parent::boot();

        static::addGlobalScope($softDeleteColumn, function (Builder $builder) use ($softDeleteColumn) {
            $builder->where($builder->getModel()->getTable().'.'.$softDeleteColumn, '!=', '0');
        });

        // add in custom deleting
        static::deleting( function($model) use ($softDeleteColumn) {
            // save custom delete value
            $model->attributes[$softDeleteColumn] = 0;
            $model->save();
            return false;
        });

        static::creating(function ($model) {
            if (empty($model->fk_criador_id)) {
                $model->fk_criador_id = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (empty($model->fk_atualizador_id)) {
                $model->fk_atualizador_id = auth()->id();
            }
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithTrashed(Builder $query) {
        $softDeleteColumn = static::SOFT_DELETE ? static::SOFT_DELETE : 'status';
        return $query->withoutGlobalScope($softDeleteColumn);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyTrashed(Builder $query) {
        $softDeleteColumn = static::SOFT_DELETE ? static::SOFT_DELETE : 'status';
        return $query->withoutGlobalScope($softDeleteColumn)->where($softDeleteColumn, 0);
    }
}
