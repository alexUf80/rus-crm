<?php
error_reporting(-1);
ini_set('display_errors', 'On');
chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class CheckPaymentsCron extends Core
{
    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    private function run()
    {
        // $to_time = date('Y-m-d 00:00:00');
        // $from_time = date('Y-m-d 23:59:00');

        // $query = $this->db->placehold("
        //     SELECT *
        //     FROM __transactions
        //     WHERE callback_response is null
        //     AND created >= ?
        //     AND created <= ?
        //     and `sector` not in (9748, 8079)
        //     ORDER BY id DESC
        // ", $from_time, $to_time);
        // $this->db->query($query);

        // $transactions = $this->db->results();

        // if (!empty($transactions)) {
        //     foreach ($transactions as $t) {
        //         if (!empty($t->register_id)) {
        //             $url = $this->config->front_url . '/best2pay_callback/payment?id=' . $t->register_id;
        //             file_get_contents($url);
        //             usleep(100000);
        //         }
        //     }
        // }



        $query = $this->db->placehold("
            SELECT *
            FROM __transactions
            WHERE callback_response is null
            AND `sector` not in (9748, 8079)
            AND operation IS NOT null
            ORDER BY id DESC
        ");
        $this->db->query($query);

        $transactions = $this->db->results();

        foreach ($transactions as $transaction) {
            $operation_info = $this->Best2pay->get_operation_info($transaction->sector, $transaction->register_id, $transaction->operation);
            var_dump($operation_info);

            $this->transactions->update_transaction($transaction->id, array(
                'callback_response' => $operation_info
            ));
        }
    }


}

new CheckPaymentsCron();