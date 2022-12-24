<?php

class TransactionsORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_transactions';
    protected $guarded = [];
    public $timestamps = false;
}