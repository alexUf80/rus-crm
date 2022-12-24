<?php

class LeadFinancesPostbacks implements ApiInterface
{
    protected static $link = 'https://offers.leads.tech/add-conversion';

    public static function sendRequest($request)
    {
        $status = $request->status;
        $clickHash = $request->click_hash;
        $goalId = $request->goalId;
        $transactionId = $request->transactionId;

        self::$link = self::$link . '?click_id=' . $clickHash . '&goal_id=' . $goalId . '&status=' . $status . '&transaction_id='.$transactionId.'&sumConfirm=2500';
        return self::curl(self::$link);
    }

    public static function curl($link)
    {
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        return self::response($res);
    }

    public static function response($response)
    {
        self::toLogs($response);
        $response = json_decode($response, true);
        return $response;
    }

    public static function toLogs($log)
    {
        $insert =
            [
                'className' => self::class,
                'log' => $log,
                'params' => self::$link
            ];

        LogsORM::insert($insert);
    }
}