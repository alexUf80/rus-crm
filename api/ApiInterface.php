<?php

interface ApiInterface
{
    public static function sendRequest($request);
    public static function curl($params);
    public static function response($response);
    public static function toLogs($log);
}