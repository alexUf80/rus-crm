<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');

require 'autoload.php';

class TelegramCron extends Core
{
    
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
 
        // Баланс с б2п
        $CreditBalance = $this->Best2pay->CreditBalance();
        $xml = simplexml_load_string($CreditBalance);

        // Отправка в Телеграм
        $token = "5736054941:AAE9UXmiUv6WwyoDJPwOTRpGXxFOAUcz3Ww";

        $getQuery = array(
            "chat_id" 	=> -962979995,
            "text"  	=> "Остаток на счете: " . number_format(json_decode($xml->amount)/100, 2, ',', ' ').' ₽',
            "parse_mode" => "html",
        );
        $ch = curl_init("https://api.telegram.org/bot". $token ."/sendMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $resultQuery = curl_exec($ch);
        curl_close($ch);

        echo $resultQuery;

        exit;
    }
 
}

$cron = new TelegramCron();
$cron->run();
