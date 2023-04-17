<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__) . '/../');

require 'autoload.php';

class ExchangeOnecCron extends Core
{
    public function __construct()
    {
        parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        $i = 5;
        do {
            $run_result = $this->send_taxings();
            $i--;
        }
        while ($i > 0 && !empty($run_result));

        $i = 5;
        do {
            $run_result = $this->send_payments();
            $i--;
        }
        while ($i > 0 && !empty($run_result));
    }
    
    private function send_taxings()
    {
        $this->db->query("
            SELECT DATE(o.created) AS created
            FROM __operations AS o
            WHERE type IN ('PENI', 'PERCENTS')
            AND sent_status = 0
            AND o.amount > 0
            ORDER BY o.created ASC
            LIMIT 10
        ");
        if ($min_date = $this->db->result('created'))
            Onec::sendTaxingWithPeni($min_date);
        
        return $min_date;
    }

    private function send_payments()
    {
        $this->db->query("
            SELECT 
                o.id,
                o.order_id,
                o.created,
                o.amount,
                t.register_id,
                t.operation,
                t.loan_body_summ,
                t.loan_percents_summ,
                t.loan_peni_summ,
                t.prolongation,
                c.number AS contract_number,
                c.close_date
            FROM __operations AS o
            LEFT JOIN s_transactions AS t
            ON t.id = o.transaction_id
            LEFT JOIN s_contracts AS c
            ON c.id = o.contract_id
            WHERE o.type IN ('PAY')
            AND o.sent_status = 0
            AND o.amount > 0
            AND c.id IS NOT NULL
            ORDER BY o.created ASC
            LIMIT 10
        ");
        if ($payments = $this->db->results())
        {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($payments);echo '</pre><hr />';exit;
            foreach ($payments as $payment)
            {
                $result = Onec::sendPayment($payment);
                
            }
        }

        return $payments;
exit;


        
        
            $payment = new stdClass();
            $payment->id = $operation->id;
            $payment->date = date('Y-m-d H:i:s', strtotime($operation->created));
            $payment->contract_id = $operation->contract_id;
            $payment->order_id = $operation->contract_id;
            $payment->prolongation = $transaction->prolongation;
            $payment->closed = ($transaction->prolongation == 1) ? 0 : 1;
            $payment->prolongationTerm = ($transaction->prolongation == 1) ? 30 : 0;
            $payment->od = $transaction->loan_body_summ;
            $payment->prc = $transaction->loan_percents_summ;
            $payment->peni = $transaction->loan_peni_summ;
            $payment->register_id = $transaction->register_id;
            $payment->operation_id = $transaction->operation;

    }
}

$cron = new ExchangeOnecCron();
