<?php

class RemindersORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_reminders';
    protected $guarded = [];
    public $timestamps = false;

    public function segment()
    {
        return $this->hasOne(RemindersSegmentsORM::class, 'id', 'segmentId');
    }

    public function event()
    {
        return $this->hasOne(RemindersEventsORM::class, 'id', 'eventId');
    }
}