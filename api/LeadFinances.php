<?php

class LeadFinances implements ApiInterface
{
    protected static $link = 'https://api.gate.leadfinances.com/v1/lead/add';
    protected static $token = 'b788d4d7041148fc9c63d6144720b5ad';
    protected static $params;


    public static function sendRequest($userId)
    {
        $user = UsersORM::with(['regAddress', 'factAddress'])->find($userId)->toArray();
        list($passport_serial, $passport_number) = explode('-', $user['passport_serial']);

        self::$params =
            [
                'token' => self::$token,
                'first_name' => $user['firstname'],
                'middle_name' => $user['patronymic'] ?? '',
                'last_name' => $user['lastname'],
                'phone' => $user['phone_mobile'],
                'email' => $user['email'],
                'birthday' => date('Y-m-d', strtotime($user['birth'])),
                'type' => 1,
                'policy_accept' => 1,
                'mailings_accept' => 1,
                'city_fact' => $user['reg_address']['region'],
                'region_fact' => $user['reg_address']['region'],
                'series_passport' => $passport_serial,
                'number_passport' => $passport_number,
                'date_issue_passport' => date('Y-m-d', strtotime($user['passport_date'])),
                'issued_by_passport' => $user['passport_issued'],
                'channel_id' => 1,
                'channel_name' => 'otkaz'
            ];

        return self::curl(self::$params);
    }

    public static function curl($params)
    {
        $headers =
            [
                'Content-Type: multipart/form-data'
            ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::$link,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_CUSTOMREQUEST => 'POST'
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return self::response($response);
    }

    public static function response($response)
    {
        $response = self::decode($response);
        self::toLogs($response);
        return $response;
    }

    public static function toLogs($log)
    {
        $insert =
            [
                'className' => self::class,
                'log' => $log,
                'params' => json_encode(self::$params)
            ];

        LogsORM::insert($insert);
    }

    private static function decode($string)
    {

        $string = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $string);

        return $string;
    }
}