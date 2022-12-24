<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require('../autoload.php');

class ImportUsers extends Core
{
    private $import_dir;
    
    private $statuses = array(
    'ACTIVE' => '5',
    'CANCELED' => '3',
    'CANCELED_AFTER_APPROVE' => '3',
    'CHECK_BEGIN' => '3',
    'DECLINED' => '3',
    'DECLINED_AFTER_MANUAL' =>  '3',
    'PAID_INFULL'  => '7',
    'PAID_IN_FULL' => '7',
    'PREVIEW' => '0',
    'APPROVED_WITH_CHANGES' => '3',    
    );
    
    public function __construct()
    {
    	parent::__construct();
exit;        
        $this->import_dir = $this->config->root_dir.'base/eco/';
        
        $this->run();
    }
    
    
    private function run()
    {
        $content = array_map('trim', file($this->import_dir.'active_contracts.csv'));
        if ($this->request->get('test'))
        {
            foreach ($content->results as $item)
                if (!empty($item->main_debt) && $item->amount > $item->main_debt)
                {
                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
                    exit;
                }
        }
        else
        {
            foreach ($content as $index => $item)
            {
                if ($index > 0)
                    $this->import_item($item);
            }
        }
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content->results);echo '</pre><hr />';    
    }
    
    private function import_item($item)
    {
        $item = explode(';', $item);
        
        if ($item[13] != 'ACTIVE')
        {
            $this->db->query("SELECT * FROM __contracts WHERE number = ?", $item[2]);
            if (!($contract = $this->db->result()))
            {
                $this->db->query("SELECT * FROM __contracts WHERE outer_id = ?", $item[2]);
                $contract = $this->db->result();
            }
            
            if (!empty($contract))
            {

echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($contract);echo '</pre><hr />';                

                $this->orders->update_order($contract->order_id, array('status' => 7));
                
                $this->contracts->update_contract($contract->id, array(
                    'status' => 3,
                    'close_date' => date('Y-m-d H:i:s'),
                    'loan_body_summ' => 0,
                    'loan_percents_summ' => 0,
                    'loan_charge_summ' => 0,
                    'loan_peni_summ' => 0,
                    'collection_status' => 0,
                    'collection_manager_id' => 0,
                    'number' => $item[4],
                    'outer_id' => $item[2],
                ));
                
            }
        }
        /*
        if ($item[13] == 'ACTIVE')
        {
            $this->db->query("SELECT * FROM __contracts WHERE number = ?", $item[4]);
            $contract = $this->db->result();
            if (!empty($contract->id))
            {
                $period = (strtotime(date('2022-01-03')) - strtotime($item[3]))/86400;
                $loan_percents_summ = $period * $item[9] * 0.01 - $item[25];
                $update = new StdClass();
                $update->loan_body_summ = $item[26];
                $update->loan_percents_summ = $loan_percents_summ;
                $update->uid = $item[5];
                $update->number = $item[4];
                $update->outer_id = $item[2];
        
                $this->contracts->update_contract($contract->id, $update);
  
                $this->operations->add_operation(array(
                    'contract_id' => $contract->id,
                    'user_id' => $contract->user_id,
                    'order_id' => $contract->order_id,
                    'transaction_id' => 0,
                    'type' => 'IMPORT',
                    'amount' => $item[26] + $loan_percents_summ,
                    'created' => date('Y-m-d H:i:s'),
                    'sent_status' => 3
                ));
                echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
            }
            else
            {
                echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('NOT FOUND', $item);echo '</pre><hr />';
                
            }
            
//            exit;
        }
        */
    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __orders");
//        $this->db->query("TRUNCATE TABLE __contracts");
    }
    
}

new ImportUsers();