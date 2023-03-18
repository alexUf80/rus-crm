<?php

class BlacklistORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_blacklist';
    protected $guarded = [];
    public $timestamps = false;
}