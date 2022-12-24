<?php

class PromocodesORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_promocodes';
    protected $guarded = [];
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(OrdersORM::class, 'promocode_id','id');
    }
}