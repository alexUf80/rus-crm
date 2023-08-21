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
        
        if ($this->request->get('test'))
            $this->send_services();
        else
            $this->run();
    }
    
    private function run()
    {    
        $this->send('send_contracts');
        $this->send('send_taxings');
        $this->send('send_payments');
        $this->send('send_services');
    }
    
    private function send($methodname)
    {
        $i = 5;
        do {
            $run_result = $this->$methodname();
            $i--;
        }
        while ($i > 0 && !empty($run_result));
    }
    
    private function send_contracts()
    {
        $this->db->query("
            SELECT *
            FROM __contracts AS c
            WHERE sent_status = 0
            AND status IN (2, 3, 4)
            LIMIT 10
        ");
        if ($contracts = $this->db->results())
        {
            foreach ($contracts as $contract)
                Onec::send_loan($contract->order_id);
        }
            
        return $contracts;
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
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($min_date);echo '</pre><hr />';        
        return $min_date;
    }

    private function send_payments()
    {
        $this->db->query("
            SELECT 
                o.id,
                o.order_id,
                o.contract_id,
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
            WHERE o.type IN ('PAY', 'RECURRENT')
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

    }
    
    /**
     * ExchangeOnecCron::send_services()
     * 
        $item->Дата = date('Ymd000000', strtotime($service->date));
        $item->Клиент_id = (string)$service->user_id;
        $item->Сумма = $service->insurance_cost;
        $item->НомерДоговора = (string)$service->number;
        $item->Операция_id = (string)$service->crm_operation_id;
        $item->Страховка = $service->is_insurance;
        $item->OrderID = $service->order_id;
        $item->OperationID = $service->operation_id;
        $item->НомерКарты = $service->card_pan;

     * @return void
     */
    private function send_services()
    {
        $this->db->query("
            SELECT
                o.id,
                o.type,
                o.user_id,
                o.order_id,
                o.amount,
                c.number,
                c.card_id,
                t.register_id, 
                t.operation,
                t.created,
                t.callback_response,
                i.number AS polis
            FROM s_operations AS o
            LEFT JOIN s_transactions AS t
            ON t.id = o.transaction_id
            LEFT JOIN s_contracts AS c
            ON c.id = o.contract_id
            LEFT JOIN s_insurances AS i
            ON o.id = i.operation_id
            WHERE o.sent_status = 0
            AND o.type IN (
                'BUD_V_KURSE',
                'INSURANCE_BC',
                'INSURANCE',
                'REJECT_REASON'
            )
            AND t.id IS NOT NULL
            LIMIT 10
        ");
        
        if ($items = $this->db->results())
        {
            foreach ($items as $item)
            {
                $xml = simplexml_load_string($item->callback_response);
                
                $service = new StdClass();
                $service->date = $item->created;
                $service->user_id = $item->user_id;
                $service->insurance_cost = $item->amount;
                $service->number = empty($item->polis) ? '' : $item->polis;
                $service->crm_operation_id = $item->id;
                $service->is_insurance = (int)in_array($item->type, ['INSURANCE_BC','INSURANCE']);
                $service->order_id = $item->register_id;
                $service->operation_id = $item->operation;
                $service->card_pan = (string)$xml->pan;
                                
                $result = Onec::send_service($service);

echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($service, $result);echo '</pre><hr />';
            }
        }
        
        return $items;
    }
}

$cron = new ExchangeOnecCron();
