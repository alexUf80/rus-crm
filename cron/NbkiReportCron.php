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
