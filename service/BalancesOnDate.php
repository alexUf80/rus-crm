<?php

chdir('..');
require 'autoload.php';

class BalancesOnDate extends Core
{
    private $response = array();
    private $password = 'AX6878EK';

    public function __construct()
    {
        $this->run();
    }

    private function run()
    {
        $date = $this->request->get('date');

        $date_from = date('Y-m-d 00:00:00', strtotime($date));
        $date_to = date('Y-m-d 23:59:59', strtotime($date));

        if (empty($date)) {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите даты в формате yyyy-mm-dd';
            $this->output();
        }

        $password = $this->request->get('password');

        if ($password != $this->password) {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите пароль обмена';
            $this->output();
        }

        $operations = array();
        $query = $this->db->placehold("
            SELECT 
                c.number,
                o.contract_id, 
                o.created,
                o.type,
                o.amount,
                o.loan_body_summ,
                o.loan_percents_summ,
                o.loan_peni_summ,
                t.loan_body_summ AS t_loan_body_summ,
                t.loan_percents_summ AS t_loan_percents_summ,
                t.loan_peni_summ AS t_loan_peni_summ
            FROM s_operations AS o
            LEFT JOIN s_contracts AS c
            ON c.id = o.contract_id
            LEFT JOIN s_transactions AS t
            ON t.id = o.transaction_id
            WHERE DATE(o.created) = ?
            AND o.type IN ('PERCENTS', 'PENI', 'PAY')
            ORDER BY o.created ASC
        ", date('Y-m-d', strtotime($date)));
        $this->db->query($query);
        foreach ($this->db->results() as $result)
        {
            if (!isset($operations[$result->contract_id]))
            {
                $operations[$result->contract_id] = [];
            }
            
            $operations[$result->contract_id][] = $result; 
        }
        
        $contract_items = [];
        foreach ($operations as $contract_operations)
        {
            $contract_item = new StdClass();
            $contract_item->НомерДоговора = $contract_operations[0]->number;
            $contract_item->ОстатокОД = 0;
            $contract_item->ОстатокПроцентов = 0;
            $contract_item->ОстатокПени = 0;
            
            foreach ($contract_operations as $contract_operation)
            {
                if ($contract_operation->type == 'PERCENTS')
                {
                    $contract_item->ОстатокОД = $contract_operation->loan_body_summ;
                    $contract_item->ОстатокПроцентов = $contract_operation->loan_percents_summ;                    
                }
                if ($contract_operation->type == 'PENI')
                {
                    $contract_item->ОстатокПени = $contract_operation->loan_peni_summ;
                }
                if ($contract_operation->type == 'PAY')
                {
                    $contract_item->ОстатокОД = round($contract_item->ОстатокОД - $contract_operation->t_loan_body_summ, 2);
                    $contract_item->ОстатокПроцентов = round($contract_item->ОстатокПроцентов - $contract_operation->t_loan_percents_summ, 2);
                    $contract_item->ОстатокПени = round($contract_item->ОстатокПени - $contract_operation->t_loan_peni_summ, 2);
                }
            }
            $contract_items[] = $contract_item;
        }
        
        $this->response['items'] = $contract_items;

        $this->output();
    }

    private function output()
    {
        header('Content-type:application/json');
        echo json_encode($this->response);
        exit;
    }
}

new BalancesOnDate();