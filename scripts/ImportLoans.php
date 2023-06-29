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
        $this->import_dir = $this->config->root_dir.'base/eco/loan/';
        
        $this->run();
    }
    
    
    private function run()
    {
        $scan = array_values(array_filter(scandir($this->import_dir), function($var){
            return $var != '.' && $var != '..';
        }));
        
        $filenumber = $this->request->get('file', 'integer');
        
        if (!isset($scan[$filenumber]))
            exit('Не найден файл');
        
//        if (!$this->request->get('test') && $filenumber == 0)
//            $this->truncate();
    
        $content = json_decode(file_get_contents($this->import_dir.$scan[$filenumber]));
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
            foreach ($content->results as $item)
            {
                $this->import_item_new($item);
            }
        }
        
        echo '<meta http-equiv="refresh" content="2;'.$this->request->url(array('file'=>$filenumber+1)).'">';
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content->results);echo '</pre><hr />';    
    }
    
    private function import_item_new($item)
    {
        $exception = array(
            'jV1EoRd5Vu', 
            'LLauCdicio', 
            'By2bxiP62c', 
            'vOp5kUUACE',
            'LhwtIeJdFw',
            'Mhal54iPrq',
            'i6Yra62k4W',
            '3H5Q3s5iph',
            'F6taIAOgZE',
        );
        
        if (in_array($item->objectId, $exception))
        {
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';        
exit;            
        }
        
        if ($item->statusName == 'ACTIVE')
        {
        }
    }

    private function import_item($item)
    {
        $this->db->query("
            SELECT id FROM s_users WHERE unload_id = ?
        ", $item->user->objectId);
        if ($user_id = $this->db->result('id'))
        {
            $order = new StdClass();
            $order->type = 'unload';
            $order->user_id = $user_id;
            $order->ip = $item->ip;
            $order->uid = $item->uid;
            $order->amount = $item->amount;
            $order->period = $item->term;
            $order->date = date('Y-m-d H:i:s', strtotime($item->createdAt));
            $order->sent_1c = 3;
            $order->id_1c = $item->objectId;
            $order->status_1c = $item->statusName;
            $order->status = $this->statuses[$item->statusName];
            
            $this->db->query("SELECT id FROM __orders WHERE id_1c = ?", $item->objectId);
            if (!($order_id = $this->db->result('id')))
                $order_id = $this->orders->add_order($order);
            
            if ($order->status == 5)
            {
                $contract = new StdClass();
                
                $contract->uid = $item->uid;
                $contract->order_id = $order_id;
                $contract->user_id = $user_id;
                $contract->number = $item->objectId;
                $contract->amount = $item->amount;
                $contract->period = $item->term;
                $contract->create_date = date('Y-m-d H:i:s', strtotime($item->createdAt));
                $contract->type = 'base';
                $contract->base_percent = 0.8;
                $contract->charge_percent = 0;
                $contract->peni_percent = 0;
                $contract->status = 2;
                $contract->sent_status = 3;
                
                if (!empty($item->next_payment_date))
                    $contract->return_date = date('Y-m-d H:i:s', strtotime($item->next_payment_date->iso));
                
                $contract->loan_body_summ = $item->main_debt;
//                $contract->loan_percents_summ = 0;
            
                $this->db->query("SELECT id FROM __contracts WHERE number = ?", $item->objectId);
                if (!($contract_id = $this->db->result('id')))
                    $contract_id = $this->contracts->add_contract($contract);
                
                $this->orders->update_order($order_id, array('contract_id'=>$contract_id));
            }
        }
        else
        {
            
        }
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';

    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __orders");
//        $this->db->query("TRUNCATE TABLE __contracts");
    }
    
}

new ImportUsers();