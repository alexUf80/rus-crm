<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require 'autoload.php';

class CommunicationsAjax extends Core
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
            
            case 'add':
                $this->action_add_communication();                
            break;
            
            case 'check':
                $this->action_check_communication();                
            break;
            
        endswitch;

        $this->json_output();
        
    }
    
    private function action_add_communication()
    {
        $user_id = $this->request->get('user_id', 'integer');

        $user = $this->users->get_user($user_id);


        $type = $this->request->get('type', 'string');
        $content = (string)$this->request->get('content');
        $from_number = (string)$this->request->get('from_number');
        $manager_id = (int)$this->request->get('manager_id');
        $to_number = (string)$user->phone_mobile;
        $yuk = (int)$this->request->get('yuk');
        
        $this->communications->add_communication(array(
            'user_id' => $user_id,
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $type,
            'content' => $content,
            'from_number' => $from_number,
            'to_number' => $to_number,
            'yuk' => (int)$yuk
        ));
    }
    
    private function action_check_communication()
    {
        $user_id = $this->request->get('user_id', 'integer');
        
        $this->response = (int)$this->communications->check_user($user_id);
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
new CommunicationsAjax();