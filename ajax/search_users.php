<?php

chdir('..');

require 'autoload.php';

class SearchUsersAjax extends Core
{
    private $query = '';
    private $response = array();
    
    private $limit = 30;
    
    public function run()
    {
        $this->query = trim($this->request->get('query'));
        
        if (empty($this->query))
        {
            $this->response['error'] = 'empty_query';
        }
        else
        {
            $this->response['query'] = $this->query;
            $this->response['suggestions'] = array();
            if ($users = $this->users->get_users(array('keyword'=>$this->query, 'limit'=>$this->limit)))
            {
                foreach ($users as $user)
                {
                    $suggestion = new StdClass();
                    $suggestion->value = $user->lastname.' '.$user->firstname.' '.$user->patronymic.' '.$user->phone_mobile;
                    $suggestion->data = $user;
                    
                    $this->response['suggestions'][] = $suggestion;
                }
            }
        }
        
        $this->output();
    }
    
    private function output()
    {
    	header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");	
        
        echo json_encode($this->response);
        exit;
    }
    
}

$ajax = new SearchUsersAjax();
$ajax->run();