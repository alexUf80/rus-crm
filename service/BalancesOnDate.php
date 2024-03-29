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
                id,
                number,
                amount,
                status,
                loan_body_summ,
                loan_percents_summ,
                loan_peni_summ
            FROM s_contracts 
            WHERE (status = 2 OR status = 3 OR status = 4)
            AND (DATE(close_date) > ? OR close_date IS null)
            AND DATE(inssuance_date) <= ?
        ", date('Y-m-d', strtotime($date)), date('Y-m-d', strtotime($date)));
        $this->db->query($query);
        $results = $this->db->results();
        foreach ($results as $result)
        {
            if (!isset($operations[$result->number]))
            {
                $operations[$result->number] = [];
            }
            
            $operations[$result->number][] = $result; 
        }
        
        $contract_items = [];
        foreach ($operations as $contract_operations)
        {
            if (!empty($contract_operations[0]->number))
            {
                // if ($contract_operations[0]->number != "0330-3907") {
                //     continue;
                // }
                $contract_item = new StdClass();
                $contract_item->НомерДоговора = $contract_operations[0]->number;

                $contract_item->ОстатокОД = "0.00";
                $contract_item->ОстатокПроцентов = "0.00";
                $contract_item->ОстатокПени = "0.00";

                $query = $this->db->placehold("
                    SELECT 
                        loan_body_summ,
                        loan_percents_summ,
                        loan_peni_summ,
                        type, 
                        created
                    FROM s_operations
                    WHERE contract_id = ?
                    AND DATE(created) = ?
                    ORDER BY created ASC
                ", $contract_operations[0]->id, date('Y-m-d', strtotime($date)));
                $this->db->query($query);
                $results = $this->db->results();

                // var_dump(count($results));

                if (count($results) == 0) {
                    $query = $this->db->placehold("
                        SELECT 
                            loan_body_summ,
                            loan_percents_summ,
                            loan_peni_summ,
                            type, 
                            created
                        FROM s_operations
                        WHERE contract_id = ?
                        AND DATE(created) <= ?
                        ORDER BY created DESC, id DESC
                        LIMIT 2
                    ", $contract_operations[0]->id, date('Y-m-d', strtotime($date)));
                    $this->db->query($query);
                    $results = $this->db->results();
                }

                // var_dump($results);

                foreach ($results as $contract_operation)
                {


                    if ($contract_operation->type == 'P2P')
                    {
                        $contract_item->ОстатокОД = $contract_operations[0]->amount;
                        $contract_item->ОстатокПроцентов = 0;
                        $contract_item->ОстатокПени = 0;
                    }
                    if ($contract_operation->type == 'PERCENTS')
                    {
                        $contract_item->ОстатокОД = $contract_operation->loan_body_summ;
                        // $contract_item->ОстатокПроцентов = $contract_operation->loan_percents_summ;                    
                        if ($contract_item->ОстатокПроцентов < $contract_operation->loan_percents_summ) {
                            $contract_item->ОстатокПроцентов = $contract_operation->loan_percents_summ;
                        }
                        if ($contract_item->ОстатокПени < $contract_operation->loan_peni_summ) {
                            $contract_item->ОстатокПени = $contract_operation->loan_peni_summ;
                        }
                    }
                    if ($contract_operation->type == 'PENI')
                    {
                        $contract_item->ОстатокОД = $contract_operation->loan_body_summ;
                        if ($contract_item->ОстатокПроцентов < $contract_operation->loan_percents_summ) {
                            $contract_item->ОстатокПроцентов = $contract_operation->loan_percents_summ;
                        }
                        $contract_item->ОстатокПени = $contract_operation->loan_peni_summ;
                    }
                    if ($contract_operation->type == 'PAY' || $contract_operation->type == 'RECURRENT' || $contract_operation->type == 'SERVICE_REFUND')
                    {
                        if (!is_null($contract_operation->loan_body_summ)) {
                            $contract_item->ОстатокОД = $contract_operation->loan_body_summ;
                        }
                        if (!is_null($contract_operation->loan_percents_summ)) {
                            $contract_item->ОстатокПроцентов = $contract_operation->loan_percents_summ;                    
                        }
                        if (!is_null($contract_operation->loan_peni_summ)) {
                            $contract_item->ОстатокПени = $contract_operation->loan_peni_summ;
                        }
                        break;
                    }
                }

                
                $contract_items[] = $contract_item;
            }
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