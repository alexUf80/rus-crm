<?php

error_reporting(-1);
ini_set('display_errors', 'On');


//chdir('/home/v/vse4etkoy2/nalic_eva-p_ru/public_html/');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

/**
 * IssuanceCron
 *
 * Скрипт выдает кредиты, и списывает страховку
 *
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class SendPostbackCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    private function run()
    {
        $crons = PostbacksCronORM::where('is_complited', 0)->get();

        foreach ($crons as $cron)
        {
            $order = OrdersORM::find($cron->order_id);

            $postback = new stdClass();
            $postback->status = $cron->status;
            $postback->click_hash = $order->click_hash;
            $postback->goalId = $cron->goal_id;
            $postback->transactionId = rand(0, 999999);

            LeadFinancesPostbacks::sendRequest($postback);

            PostbacksCronORM::find($cron->id)->update(['is_complited' => 1, 'transaction_id' => $postback->transactionId]);
        }
    }
}
$cron = new SendPostbackCron();