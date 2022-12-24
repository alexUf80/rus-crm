<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require('../autoload.php');

class ImportUsers extends Core
{
    private $import_dir;
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->import_dir = $this->config->root_dir.'base/eco/user/';
        
exit;        
        
        $this->run();
    }
    
    
    private function run()
    {
        $scan = array_values(array_filter(scandir($this->import_dir), function($var){
            return $var != '.' && $var != '..';
        }));
        
        $filenumber = $this->request->get('file', 'integer');
        
        if ($filenumber == 0)
            $this->truncate();
    
        if (!isset($scan[$filenumber]))
            exit('Не найден файл');
        
        $content = json_decode(file_get_contents($this->import_dir.$scan[$filenumber]));
        
        foreach ($content->results as $item)
        {
            $this->import_item($item);
        }

        echo '<meta http-equiv="refresh" content="2;'.$this->request->url(array('file'=>$filenumber+1)).'">';
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content->results);echo '</pre><hr />';    
    }
    
    
    private function import_item($item)
    {
        $this->db->query("SELECT id FROM __users WHERE unload_id = ?", $item->objectId);
        
        if ($user_id = $this->db->result('id'))
        {
            if (!empty($item->credit_card_scan))
            {
                $this->users->add_file(array(
                    'user_id' => $user_id,
                    'name' => $item->credit_card_scan->name,
                    'type' => 'card',
                    'status' => 4,
                ));
            }
            

            if (!empty($item->passport_scan_first))
            {
                $this->users->add_file(array(
                    'user_id' => $user_id,
                    'name' => $item->passport_scan_first->name,
                    'type' => 'passport1',
                    'status' => 4,
                ));
            }
            if (!empty($item->passport_scan_second))
            {
                $this->users->add_file(array(
                    'user_id' => $user_id,
                    'name' => $item->passport_scan_second->name,
                    'type' => 'passport2',
                    'status' => 4,
                ));
            }
            if (!empty($item->passport_scan_selfie))
            {
                $this->users->add_file(array(
                    'user_id' => $user_id,
                    'name' => $item->passport_scan_selfie->name,
                    'type' => 'face',
                    'status' => 4,
                ));
            }
        }
        
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';
//exit;
    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __users");
        $this->db->query("TRUNCATE TABLE __files");
    }
    
}

new ImportUsers();