<?php

class PostbacksCronORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_postbacks_cron';
    protected $guarded = [];
    public $timestamps = false;
}