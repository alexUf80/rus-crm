<?php

class RemindersCronORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_reminders_cron';
    protected $guarded = [];
    public $timestamps = false;
}