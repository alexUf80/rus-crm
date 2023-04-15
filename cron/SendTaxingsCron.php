<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class SendTaxingsCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        
        $i = 10;
        do {
            $run_result = $this->run();
            $i--;
        }
        while ($i > 0 && !empty($run_result));
    }


    public function run()
    {
        $this->db->query("
            SELECT DATE(o.created) AS created
            FROM __operations AS o
            WHERE type IN ('PENI', 'PERCENTS')
            AND sent_status = 0
            AND o.amount > 0
            ORDER BY o.created ASC
        ");
        if ($min_date = $this->db->result('created'))
            Onec::sendTaxingWithPeni($min_date);
        
        return $min_date;
    }
}

$cron = new SendTaxingsCron();
