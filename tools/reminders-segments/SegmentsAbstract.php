<?php

abstract class SegmentsAbstract
{
    abstract static function processing($data);

    public static function send($send)
    {
        $smsc = new Sms();

        $to = $send['phone'];
        $text = $send['msg'];

        $resp = $smsc->send($to, $text);

        $log =
            [
                'className' => self::class,
                'log' => json_encode($resp, JSON_UNESCAPED_UNICODE)
            ];

        LogsORM::insert($log);

        return $resp;
    }

    public static function short_link($contract)
    {
        $helpers = new Helpers();
        $config = new Config();

        $code = $helpers->c2o_encode($contract->id);
        $short_link = $config->main_domain . '/p/' . $code;

        return $short_link;
    }
}