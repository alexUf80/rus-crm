<?php

chdir('..');

require 'autoload.php';

class GetInfoService extends Core
{
    private $response = array();
    
    private $password = 'AX6878EK';
    
    public function __construct()
    {
    	$this->run();
    }
    
    private function run()
    {
        $password = $this->request->get('password');
        if ($password != $this->password)
            $this->output(['error'=>1, 'message' => 'Укажите пароль обмена']);            
        
        $lastname = trim($this->request->get('lastname'));
        $firstname = trim($this->request->get('firstname'));
        $patronymic = trim($this->request->get('patronymic'));
        $birth = trim($this->request->get('birth'));
        
        if (empty($lastname))
            $this->output(['error'=>1, 'message' => 'Укажите фамилию']);            
        if (empty($firstname))
            $this->output(['error'=>1, 'message' => 'Укажите имя']);            
        if (empty($patronymic))
            $this->output(['error'=>1, 'message' => 'Укажите отчество']);            
        if (empty($birth))
            $this->output(['error'=>1, 'message' => 'Укажите дату рождения (Y-m-d)']);            

        $format_birth = date('d.m.Y', strtotime($birth));

        $query = $this->db->placehold("
            SELECT 
                inn,
                id,
                contact_person_name,
                contact_person_phone,
                contact_person_relation,
                contact_person2_name,
                contact_person2_phone,
                contact_person2_relation
            FROM __users AS u
            WHERE birth = ?
            AND lastname LIKE '".$this->db->escape($lastname)."'
            AND firstname LIKE '".$this->db->escape($firstname)."'
            AND patronymic LIKE '".$this->db->escape($patronymic)."'
        ", $format_birth);
        $this->db->query($query);
        $user = $this->db->result();
        
        $this->response['success'] = 1;
        
        if (empty($user))
        {
            $this->output(['error'=>1, 'message'=>'Пользователь не найден']);
        }
        else
        {
            $response = new StdClass();
            $response->inn = $user->inn;
            $response->contactpersons = [];
            
            if (!empty($user->contact_person_name))
            {
                $response->contactpersons[] = (object)[
                    'name' => $user->contact_person_name,
                    'phone' => $user->contact_person_phone, 
                    'relation' => $user->contact_person_relation,
                ];
            }
            if (!empty($user->contact_person2_name))
            {
                $response->contactpersons[] = (object)[
                    'name' => $user->contact_person2_name,
                    'phone' => $user->contact_person2_phone, 
                    'relation' => $user->contact_person2_relation,
                ];
            }
            
            $this->output(['success'=>1, 'data' => $response]);
        }


    }
    
    private function output($data)
    {
        header('Content-type:application/json');
        echo json_encode($data);
        
        exit;
    }
}
new GetInfoService();