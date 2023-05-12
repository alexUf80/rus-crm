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
        $to_time = date('Y-m-d 00:00:00');
        $from_time = date('Y-m-d 23:59:00');

        $query = $this->db->placehold("
            SELECT *
            FROM __transactions
            WHERE callback_response is null
            AND created >= ?
            AND created <= ?
            and `sector` not in (9748, 8079)
            ORDER BY id DESC
        ", $from_time, $to_time);
        $this->db->query($query);

        $transactions = $this->db->results();

        if (!empty($transactions)) {
            foreach ($transactions as $t) {
                if (!empty($t->register_id)) {
                    $url = $this->config->front_url . '/best2pay_callback/payment?id=' . $t->register_id;
                    file_get_contents($url);
                    usleep(100000);
                }
            }
        }
    }


}

new CheckPaymentsCron();