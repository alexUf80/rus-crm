<?php

chdir('..');

require 'autoload.php';

class CurrentContractsService extends Core
{
    private $response = array();
    
    private $password = 'AX6878EK';
    
    public function __construct()
    {
    	$this->run();
    }
    
    private function run()
    {
        $password = $this->request->get('password');
        if ($password != $this->password)
        {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите пароль обмена';
            
            $this->output();            
        }
        
    	$from = $this->request->get('from');
    	$to = $this->request->get('to');
    	$date = $this->request->get('date');
        
        $date_from = date_create_from_format('YmdHis', $from)->format('Y-m-d H:i:s');        
        $date_to = date_create_from_format('YmdHis', $to)->format('Y-m-d H:i:s');
        $on_date = date_create_from_format('YmdHis', $date)->format('Y-m-d H:i:s');
        
        if (empty($date_from) || empty($date_to) || empty($on_date))
        {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите даты в формате ггггММддЧЧммсс';
            
            $this->output();
        }
        
        
        
        $query = $this->db->placehold("
            SELECT 
                c.id,
                c.number,
                c.inssuance_date,
                c.loan_body_summ,
                c.loan_percents_summ,
                c.loan_peni_summ,
                c.loan_charge_summ,
                c.status,
                c.sold,
                c.premier,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.birth
            FROM __contracts AS c
            LEFT JOIN __users AS u
            ON u.id = c.user_id
            WHERE DATE(c.inssuance_date) >= ?
            AND DATE(c.inssuance_date) <= ?
            AND status IN (2, 4, 10)
        ", $date_from, $date_to);
        $this->db->query($query);
        $contracts = $this->db->results();

        $this->response['success'] = 1;
        $this->response['from'] = $date_from;;
        $this->response['to'] = $date_to;
        $this->response['on_date'] = $on_date;

        $this->response['items'] = array();
        
        if (!empty($contracts))
        {
            foreach ($contracts as $contract)
            {
                $contract_operations = $this->operations->get_operations([
                    'contract_id' => $contract->id,
                    'date_to' => $on_date,
                    'type' => ['IMPORT_PENI', 'IMPORT_PERCENT', 'PERCENTS', 'REPAYMENT_OD', 'REPAYMENT_PENI', 'REPAYMENT_PERCENT', 'REPAYMENT_PERCENT_ADV']
                ]);
                
                if (empty($contract_operations))
                {
                    $od = $contract->loan_body_summ;
                    $percents = 0;
                    $peni = 0;
                    $charge = 0;
                }
                else
                {
                    $last_operation = end($contract_operations);
                    $od = $last_operation->loan_body_summ;
                    $percents = $last_operation->loan_percents_summ;
                    $peni = $last_operation->loan_peni_summ;
                    $charge = $last_operation->loan_charge_summ;
                    
                }
                
                $item = new StdClass();
                $item->number = $contract->number;
                $item->date = $contract->inssuance_date;
                $item->client = $contract->lastname.' '.$contract->firstname.' '.$contract->patronymic;
                $item->od = $od;
                $item->percents = $percents;
                $item->peni = $peni;
                $item->charge = $charge;

                $this->response['items'][] = $item;
            }
        }

        $this->output();
    }
    
    private function output()
    {
        header('Content-type:application/json');
        echo json_encode($this->response);
        
        exit;
    }
}
new CurrentContractsService();