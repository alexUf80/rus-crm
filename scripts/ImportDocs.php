<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require('../autoload.php');

class ImportDocs extends Core
{
    private $import_dir;
    
    public function __construct()
    {
    	parent::__construct();
//exit;        
        $this->import_dir = $this->config->root_dir.'files/import/base/';
        
        $this->run();
    }
    
    
    private function run()
    {
        $scan = array_values(array_filter(scandir($this->import_dir), function($var){
            return !in_array($var, array('.', '..', 'ids.txt', 'saved.txt'));
        }));
        
        $filenumber = $this->request->get('file', 'integer');
        
        if ($filenumber == 0)
            $this->truncate();
    
        if (!isset($scan[$filenumber]))
            exit('Не найден файл');
        
        $i = 0;
        while ($i < 100)
        {
            $content = json_decode(file_get_contents($this->import_dir.$scan[$filenumber+$i]));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content, $scan[$filenumber+$i]);echo '</pre><hr />';
            foreach ($content->results as $item)
            {
                if ($item->document_type == 'html')
                    $this->import_item($item);
                else
                    echo 'next<br />';
            }
//echo ($scan[$filenumber+$i]).'<br />';
            $i++;
        }
//        echo '<meta http-equiv="refresh" content="1;'.$this->request->url(array('file'=>$filenumber+$i)).'">';
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($content->results);echo '</pre><hr />';    
    }
    
    private function import_item($item)
    {

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item);echo '</pre><hr />';return;


        $this->db->query("
            SELECT * FROM __orders WHERE id_1c = ?
        ", $item->loan->objectId);
        if ($order = $this->db->result())
        {
            
            $doc = array(
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'contract_id' => $order->contract_id,
                'type' => 'IMPORT',
                'name' => $item->title,
                'template' => 'html.tpl',
                'content' => $item->content,
                'client_visible' => (int)!$item->hidden,
                'params' => isset($item->content_vars) ? $item->content_vars : false,
                'created' => date('Y-m-d H:i:s', strtotime($item->createdAt)),
                'sent_1c' => 3, 
            );
            $doc_id = $this->documents->add_document($doc);
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($order);echo '</pre><hr />';
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($doc_id, $doc);echo '</pre><hr />';
            
        }
        else
        {
//            throw new Exception('');
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item->loan->objectId);echo '</pre><hr />';
        
    }
    
    private function truncate()
    {
//        $this->db->query("TRUNCATE TABLE __users");
//        $this->db->query("TRUNCATE TABLE __files");
    }
    
}

new ImportDocs();