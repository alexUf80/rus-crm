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

        $date = date('Y-m-d H:i:s', strtotime('-2 seconds'));
        $hour = date('H', strtotime('-2 seconds'));
 
        // Баланс с б2п
        $CreditBalance = $this->Best2pay->CreditBalance();
        $xml = simplexml_load_string($CreditBalance);

        $token = "5736054941:AAE9UXmiUv6WwyoDJPwOTRpGXxFOAUcz3Ww";
        $chat_id = -962979995;
        $text = "Остаток на счете: " . number_format(json_decode($xml->amount)/100, 2, ',', ' ').' ₽';

        $this->Telegram->send_message($token, $chat_id, $text);


        // Финансовые показатели
        $date_from = date('Y-m-d '.'00:00:00',strtotime($date));
        $date_to = date('Y-m-d '.'23:59:59', strtotime($date));

        $hour_from = date('Y-m-d '.$hour.':00:00',strtotime($date));
        $hour_to = date('Y-m-d '.$hour.':59:59',strtotime($date));

        // выдачи
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

        // оплаты
        $query = $this->db->placehold("
            SELECT o.*, t.callback_response FROM s_operations as o
            LEFT JOIN s_transactions as t
            ON o.transaction_id = t.id
            WHERE o.type='PAY' 
            AND o.created >= ?
            AND o.created <= ?
        ", $date_from, $date_to);
        $this->db->query($query);
        $results = $this->db->results();
        
        $payments_count_day = 0;
        $payments_sum_day = 0;
        $payments_count_hour = 0;
        $payments_sum_hour = 0;

        foreach ($results as $result) {
            if(simplexml_load_string($result->callback_response)->state == 'APPROVED'){
                // $xml_result = simplexml_load_string(unserialize($result->response));
                $payments_count_day++;
                $payments_sum_day += $result->amount;

                if($result->created >= $hour_from && $result->created <= $hour_to){
                    $payments_count_hour++;
                    $payments_sum_hour += $result->amount;
                }
            }
        }
        

        $token = "5994196675:AAHM8bs6Slw150-RP4_2EOqsyTh0mGvmrmU";
        $chat_id = -921625222;

        $text = "<b><u>Остаток на счете:</u></b> " . number_format(json_decode($xml->amount)/100, 2, ',', ' ').' ₽';
        $text .= PHP_EOL;
        $text .= PHP_EOL;
        $text .= "<b><u>Данные за прошедший час</u></b>";
        $text .= PHP_EOL;
        $text .= "Количество выдач: " . $issuance_count_hour;
        $text .= PHP_EOL;
        $text .= "Сумма выдач: " . number_format(json_decode($issuance_sum_hour)/100, 2, ',', ' ').' ₽';
        $text .= PHP_EOL;
        $text .= "-----";
        $text .= PHP_EOL;
        $text .= "Количество оплат: " . $payments_count_hour;
        $text .= PHP_EOL;
        $text .= "Сумма оплат: " . number_format(json_decode($payments_sum_hour), 2, ',', ' ').' ₽';
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
        $text .= PHP_EOL;
        $text .= "-----";
        $text .= PHP_EOL;
        $text .= "Всего оплат сегодня: " . $payments_count_day;
        $text .= PHP_EOL;
        $text .= "Сумма всех оплат за сегодня: " . number_format(json_decode($payments_sum_day), 2, ',', ' ').' ₽';
        $text .= PHP_EOL;
        $text .= $date;
        
        $this->Telegram->send_message($token, $chat_id, $text);


        exit;
    }
 
}

$cron = new TelegramCron();
$cron->run();
