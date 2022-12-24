<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require('../autoload.php');

class ImportOperations extends Core
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
        
        $this->import_dir = $this->config->root_dir.'base/eco/';
        
        $this->run();
    }
    
    
    private function run()
    {
        $content = array_map('trim', file($this->import_dir.'old_crm_contracts_20220105.csv'));
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
        $headers = array(
            'Дата_выдачи',
            'Номер_договора',
            'УИД',
            'Заемщик',
            'Сумма',
            'СРОК',
            'Ставка',
            'Статус',
            'ID',
            'Наличие погашений',
            'Общая_сумма_задолженности',
            'Дата_платежа',
            'Остаток_ОД'
        );
        
        $item = explode(';', $item);
        
        $item = array_combine($headers, $item);
        if ($item['Статус'] == 'ACTIVE')
        {
            $this->db->query("SELECT * FROM __contracts WHERE outer_id = ?", $item['ID']);
            if ($contract = $this->db->result())
            {
                $update = array();
                if ($item['Наличие погашений'] > 0)
                {
                    $update['allready_paid'] = $item['Наличие погашений'];
                    
//                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
                }
                if (strtotime(date('Y-m-d', strtotime($contract->return_date))) < strtotime($item['Дата_платежа']) )
                {
                    $update['return_date'] = date('Y-m-d H:i:s', strtotime($item['Дата_платежа']));
                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$old_date', $contract->return_date, 'new_date', $item['Дата_платежа']);echo '</pre><hr />';
                }

                if (!empty($update))
                    $this->contracts->update_contract($contract->id, $update);
                /*
                if (strtotime(date('Y-m-d', strtotime($contract->return_date))) > strtotime($item['Дата_платежа']) )
                {
                
                    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($contract, $item);echo '</pre><hr />';
                }
                */
            }
            else
            {
                // APPROVED_WITH_CHANGES
                $exception = array(
                    'jV1EoRd5Vu', 
                    'LLauCdicio', 
                    'By2bxiP62c', 
                    'vOp5kUUACE',
                    'LhwtIeJdFw',

                    'Mhal54iPrq', // loans11000::691                                        
                    '3H5Q3s5iph', // loans1000::8
                    'i6Yra62k4W', // loans0::79
                    'F6taIAOgZE', // loans0::386
                );
                
                
                if (!in_array($item['ID'], $exception))
                    exit('Контракт не найден '.$item['ID']);
            }
        }
        
        
        
        
        
        
    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __orders");
//        $this->db->query("TRUNCATE TABLE __contracts");
    }
    
}

new ImportOperations();