<?php

class OrdersORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_orders';
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(UsersORM::class, 'id','user_id');
    }

    public function manager()
    {
        return $this->hasOne(ManagerORM::class, 'id','manager_id');
    }

    public function promocode()
    {
        return $this->hasOne(PromocodesORM::class, 'id','promocode_id');
    }
}