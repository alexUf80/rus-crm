<?php

interface ToolsInterface
{
    public static function request($request);
    public static function processing($params);
    public static function response($resp);
    public static function toLogs($log);
}