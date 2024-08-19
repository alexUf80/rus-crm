<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');

require 'autoload.php';

class NbkiReportCron extends Core
{
    private $filename = '';
    
    public function __construct()
    {
    	parent::__construct();
        
        $files = scandir($this->config->root_dir.'files/nbki/',  SCANDIR_SORT_DESCENDING);
        $files = array_filter($files, function($var){
            return !in_array($var, ['.', '..', '.htaccess']);
        });
        foreach ($files as $file)
        {
            $filemdate = date('Y-m-d', filemtime($this->config->root_dir.'files/nbki/'.$file));
            if (date('Y-m-d') == $filemdate)
                $this->filename = $this->config->root_dir.'files/nbki/'.$file;        
        }
    }
    
    public function run()
    {
        $date_from = date('Y-m-d', time() - 2 * 86400);
        $date_to = date('Y-m-d', time() - 1 * 86400);

        $this->db->query("
            SELECT * FROM __operations 
            WHERE type IN ('P2P', 'PAY')
            AND DATE(created) >= ?
            AND DATE(created) <= ?
        ", $date_from, $date_to);
        
        $operations = $this->db->results();

        foreach ($operations as $operation) {
            if ($operation->type == 'PAY' && !is_null($operation->contract_id)){
                
                $contract = $this->contracts->get_contract($operation->contract_id);

                if ($contract->close_date && date('Y-m-d', strtotime($operation->created)) == date('Y-m-d', strtotime($contract->close_date))) {

                    $operation->type = 'CLOSE';
                }
            }
        }

        // ЦЕССИЯ 
        $this->db->query("
            SELECT * FROM `s_contracts` 
            WHERE status IN (7)
            AND DATE(cession) >= ?
            AND DATE(cession) <= ?
            order by status
        ", $date_from, $date_to);


        $contracts = $this->db->results();

        foreach ($contracts as $contract) {
            $ret_date = date('Y-m-d', strtotime($contract->return_date) + 86400);
            
            $operation = $this->operations->get_operations(['order_id' => $contract->order_id, 'type' => 'PENI', 'date_from' => $ret_date, 'date_to' => $ret_date]);
            $operation[0]->type = 'CESSIA';

            $operations[] = $operation[0];

        }

        // КРЕДИТНЫЕ КАНИКУЛЫ 
        $this->db->query("
            SELECT * FROM `s_contracts` 
            WHERE status IN (2)
            AND DATE(canicule) >= ?
            AND DATE(canicule) <= ?
            order by status
        ", $date_from, $date_to);


        $contracts = $this->db->results();


        foreach ($contracts as $contract) {
            $ret_date = date('Y-m-d', strtotime($contract->canicule) - 1 * 86400);

            $operation = $this->operations->get_operations(['order_id' => $contract->order_id, 'type' => 'PERCENTS', 'date_from' => $ret_date, 'date_to' => $ret_date]);
            $operation[0]->type = 'CANICULE';

            $operations[] = $operation[0];

        }

        // ПРОСРОЧКА
        $date_from1 = $date_from;
        $date_to1 = $date_to;

        while ($date_from1 <= $date_to1) {

            $ret_date = date('Y-m-d', strtotime($date_from1) - 1 * 86400);

            $this->db->query("
                SELECT * FROM `s_contracts` 
                WHERE status IN (4)
                AND DATE(return_date) = ?
                order by return_date DESC
                #limit 1
            ", $ret_date);

            $contracts = $this->db->results();
        

            foreach ($contracts as $contract) {

                $ret_date = date('Y-m-d', strtotime($contract->return_date) + 1 * 86400);
                
                $operation = $this->operations->get_operations(['order_id' => $contract->order_id, 'type' => 'P2P', 'date_from' => date('Y-m-d', strtotime($contract->inssuance_date)), 'date_to' => date('Y-m-d', strtotime($contract->inssuance_date))]);
                $operation[0]->type = 'PENI';

                $operations[] = $operation[0];

            }

            $prosr_date = date('Y-m-d', strtotime($date_from1));
            $begin_date = '2023-01-01';
            while ($begin_date <= $prosr_date) {
                $prosr_date = date('Y-m-d', strtotime($prosr_date) - 30 * 86400);
                
                $this->db->query("
                SELECT * FROM `s_contracts` 
                WHERE status IN (4)
                AND DATE(return_date) = ?
                order by return_date DESC
                #limit 1
                ", $prosr_date);
                
                $contracts = $this->db->results();
                
                if ($contracts) {
                    foreach ($contracts as $contract) {
                        
                        $ret_date = date('Y-m-d', strtotime($contract->return_date) + 1 * 86400);
                        
                        $operation = $this->operations->get_operations(['order_id' => $contract->order_id, 'type' => 'P2P', 'date_from' => date('Y-m-d', strtotime($contract->inssuance_date)), 'date_to' => date('Y-m-d', strtotime($contract->inssuance_date))]);
                        $operation[0]->type = 'PENI';

                        $operations[] = $operation[0];
        
                    }
                    
                }

            }

            $date_from1 = date('Y-m-d', strtotime($date_from1) + 1 * 86400);
        }

        
        // Формирование отчета 
        $report = $this->nbki_report->send_operations($operations);
        
        if (!empty($report->filename))
        {
            $this->save_report($report);
            
            $this->nbki_report->add_report([
                'name' => date('d.m.Y', strtotime($date_from)).' - '.date('d.m.Y', strtotime($date_to)),
                'filename' => $report->filename,
                'created' => date('Y-m-d H:i:s'),
                'date_from' => date('Y-m-d', strtotime($date_from)),
                'date_to' => date('Y-m-d', strtotime($date_to)),
            ]);
        }
        exit;
    }
    

    public function save_report($report)
    {
        $this->filename = $this->config->root_dir.'files/nbki/'.$report->filename;
        
        $fp = fopen($this->filename, 'w+');
        flock($fp, LOCK_EX);
    
        fwrite($fp, iconv('utf8', 'cp1251', $report->data));
        flock($fp, LOCK_UN);
    }
}

$cron = new NbkiReportCron();
$cron->run();
