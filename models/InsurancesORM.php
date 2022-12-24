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
        $number .= 'NZI'; // код продукта (не меняется)
        $number .= '383'; // код партнера (не меняется)

        $polis_number = str_pad($id, 7, '0', STR_PAD_LEFT);

        $number .= $polis_number;

        return $number;
    }

    public static function get_insurance_cost($amount)
    {
        if ($amount <= 10000)
            return 390;
        elseif ($amount >= 10001 && $amount <= 20000)
            return 490;
        elseif ($amount >= 20000)
            return 590;
    }
}