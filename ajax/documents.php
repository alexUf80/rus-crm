<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require 'autoload.php';

class DocumentsAjax extends Core
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    
    public function run()
    {
        if ($href = $this->request->get('href'))
        {
            $file = file_get_contents($link);
            
            
//            header('Content-type: application/pdf');
            echo $file;
        }
        else
        {
            $document_id = $this->request->get('document', 'integer');
            $user_id = $this->request->get('user', 'integer');
        
            $link = $this->config->front_url.'/document/'.$user_id.'/'.$document_id;
        
            $file = file_get_contents($link);
        
            header('Content-type: application/pdf');
            echo $file;
        }
    }
    
}
new DocumentsAjax();