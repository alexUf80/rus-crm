<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require 'autoload.php';

class GetInfoAjax extends Core
{
    private $response = array();
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
        
    }
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');
        
        switch ($action):
            
            case 'movements':
                
                $this->action_movements();
                
            break;
            
            case 'fssp':
                
                $this->action_fssp();
                
            break;
            
        endswitch;

        $this->json_output();
        
    }
    
    private function action_fssp()
    {
    	$scoring_id = $this->request->get('scoring_id', 'integer');
        
        $scoring = $this->scorings->get_scoring($scoring_id);
        $scoring->body = unserialize($scoring->body);
        
        $this->response = $scoring;
    }
    
    
    private function action_movements()
    {
        $number = $this->request->get('number');
        
        $response = $this->soap1c->get_movements($number);
        $data = array();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump();echo '</pre><hr />';
        foreach ($response as $item)
        {
            $data_item = new StdClass();
            
            $data_item->date = date('d.m.Y', strtotime($item->Дата));
            $data_item->start_total_summ = $item->НачальныйОстаток;
            $data_item->added_body_summ = $item->НачисленоОД;
            $data_item->paid_body_summ = $item->ОплаченоОД;
            $data_item->added_percents_summ = $item->НачисленоПроцент;
            $data_item->paid_percents_summ = $item->ОплаченоПроцент;
            $data_item->added_peni_summ = $item->НачисленоПени;
            $data_item->paid_peni_summ = $item->ОплаченоПени;
            $data_item->added_charge_summ = $item->НачисленоОтветственность;
            $data_item->paid_charge_summ = $item->ОплаченоОтветственность;
            $data_item->finish_total_summ = $item->КонечныйОстаток;
//                    $data_item->conditional = (int)$item->УсловнаяОплата;
            
            $data[] = $data_item;
        }
        $this->response = $data;
    	
    }
    
    
    private function json_output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");	
        
        echo json_encode($this->response);
    }
}
new GetInfoAjax();