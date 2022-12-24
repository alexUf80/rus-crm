<?php

class ManagerORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_managers';
    protected $guarded = [];
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(OrdersORM::class, 'manager_id','id');
    }
}