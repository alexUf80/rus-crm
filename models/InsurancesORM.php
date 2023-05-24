<?php

class InsurancesORM extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's_insurances';
    protected $guarded = [];
    public $timestamps = false;

    public static function create_number($id)
    {
        $number = '';
        $number .= date('y'); // год выпуска полиса
        $number .= '0H3'; // код подразделения выпустившего полис (не меняется)
        $number .= 'NSI'; // код продукта (не меняется)
        $number .= '496'; // код партнера (не меняется)

        $polis_number = str_pad($id, 7, '0', STR_PAD_LEFT);

        $number .= $polis_number;

        return $number;
    }

    public static function get_insurance_cost($amount)
    {
        return $amount * 0.1;
    }
}