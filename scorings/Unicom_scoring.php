<?php

class Unicom_scoring extends Core
{
    private $login;
    private $password;
    
    private $url = 'https://unicom24.ru/api/phone_check/v1/';

    public function __construct()
    {
        parent::__construct();
         
    	$this->login = $this->settings->apikeys['unicom']['login'];
    	$this->password = $this->settings->apikeys['unicom']['password'];
    }
    
    public function run($audit_id, $user_id, $order_id)
    {
        echo __METHOD__;
    }
    
    public function test()
    {
        $post_data_array = array( 
            'surname' => 'Иванов', 
            'name'=> 'Михаил', 
            'mobile_phone' => '9220001122', 
            'patronymic'=>'Александрович', 
            'passport'=>'0000111222'    
        );  
        $post_data = http_build_query($post_data_array);  
        
        $http_headers =  array( 
            'Content-Type: application/x-www-form-urlencoded', 
            'Content-Length: ' . strlen($post_data) 
        );  
        
        $ch = curl_init('https://unicom24.ru/api/phone_check/v1/create/');  
        
        curl_setopt($ch, CURLOPT_POST, 1 ); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $this->login.":".$this->password); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false ); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);  
        
        $result = curl_exec($ch);  
        
        if(!$result) 
        { 
            curl_error($ch); 
        } 
        else 
        { 
            $output_var = json_decode($result); 
            var_dump($output_var); 
            exit; 
        } 
        
    }

    
    public function send($data)
    {
        $nonce = sha1(uniqid(true));
        $password = sha1($nonce.$this->token);
        
        $headers = array(
            'Content-Type: application/json',
            'username: '.$this->username,
            'nonce: '.$nonce,
            'password: '.$password,
        );
        
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        $ch = curl_init($this->url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        $result = json_decode($result);
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($info, $result);echo '</pre><hr />';
        
        return $result;
    }
}