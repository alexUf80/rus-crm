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

        $token = "5736054941:AAE9UXmiUv6WwyoDJPwOTRpGXxFOAUcz3Ww";
        $chat_id = -962979995;
        $text .= "Остаток на счете: " . number_format(json_decode($xml->amount)/100, 2, ',', ' ').' ₽';

        $this->Telegram->send_message($token, $chat_id, $text);


        // Финансовые показатели
        $hour = date('h');
        $hour = $hour < 10 ? '0' . $hour : $hour;

        $date_from = date('Y-m-d '.'00:00:00');
        $date_to = date('Y-m-d '.'23:59:59');

        $hour_from = date('Y-m-d '.$hour.':00:00');
        $hour_to = date('Y-m-d '.$hour.':59:59');

        $query = $this->db->placehold("
            SELECT * 
            FROM __p2pcredits
            WHERE complete_date >= ?
            AND  complete_date <= ?
        ", $date_from, $date_to);
        $this->db->query($query);
        $results = $this->db->results();
        
        $issuance_count_day = 0;
        $issuance_sum_day = 0;
        $issuance_count_hour = 0;
        $issuance_sum_hour = 0;

        foreach ($results as $result) {
            if($result->status == 'APPROVED'){
                $xml_result = simplexml_load_string(unserialize($result->response));
                $issuance_count_day++;
                $issuance_sum_day += $xml_result->amount;

                if($result->complete_date >= $hour_from && $result->complete_date <= $hour_to){
                    $issuance_count_hour++;
                    $issuance_sum_hour += $xml_result->amount;
                }
            }
        }
        

        $token = "5994196675:AAHM8bs6Slw150-RP4_2EOqsyTh0mGvmrmU";
        $chat_id = -921625222;

        $text = "Остаток на счете: " . number_format(json_decode($xml->amount)/100, 2, ',', ' ').' ₽';
        $text .= PHP_EOL;
        $text .= PHP_EOL;
        $text .= "<b><u>Данные за прошедший час</u></b>";
        $text .= PHP_EOL;
        $text .= "Количество выдач: " . $issuance_count_hour;
        $text .= PHP_EOL;
        $text .= "Сумма выдач: " . number_format(json_decode($issuance_sum_hour)/100, 2, ',', ' ').' ₽';
        $text .= PHP_EOL;
        $text .= PHP_EOL;
        // $text .= "Сумма выданная за период: (период требуется уточнить)" ;
        // $text .= PHP_EOL;
        // $text .= PHP_EOL;
        $text .= "<b><u>Сумма всех выдач за день</u></b>";
        $text .= PHP_EOL;
        $text .= "Всего выдано сегодня: " . $issuance_count_day;
        $text .= PHP_EOL;
        $text .= "Сумма всех выдач за сегодня: " . number_format(json_decode($issuance_sum_day)/100, 2, ',', ' ').' ₽';
        
        $this->Telegram->send_message($token, $chat_id, $text);


        exit;
    }
 
}

$cron = new TelegramCron();
$cron->run();
