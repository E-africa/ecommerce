<?php

namespace App\Model;

use App\CPU\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{

    protected $casts = [
        'status' => 'integer',
        'brand_products_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function brandProducts()
    {
        return $this->hasMany(Product::class)->active();
    }

    public function translations()
    {
        return $this->morphMany('App\Model\Translation', 'translationable');
    }

    public function getNameAttribute($name)
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/seller')) {
            return $name;
        }

        if (strpos(url()->current(), '/api')) {
            $translation = DB::table('translations')
                ->where(['translationable_type' => 'App\Model\Brand', 'translationable_id' => $this->id])
                ->where(['locale' => App::getLocale()])->first();
            if (isset($translation)){
                return $translation->value;
            }
            return $name;
        }

        return $this->translations[0]->value??$name;
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function($query){
                return $query->where('locale', Helpers::default_lang());
            }]);
        });
    }
}
