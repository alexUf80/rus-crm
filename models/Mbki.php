<?php

class Mbki extends Core
{
    private $login = 'Кронос';
    private $password = 'testAutomatUser';
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->login = $this->settings->apikeys['mbki']['login'];
        $this->password = $this->settings->apikeys['mbki']['password'];
    }
    
    public function auth()
    {
        $data = array(
            'Type' => 'Login',
            'Login' => $this->login,
            'Password' => $this->password,
        );
        
        $resp = $this->send($data);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';
        return $resp;
    }
    
    public function send($data)
    {
        $url = 'https://ssl.croinform.ru:450/api.test';
        
        $headers = array(
            'Content-type: application/x-www-form-urlencoded;charset=utf-8',
        );
        
        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

//        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
//        curl_setopt($ch, CURLOPT_CAINFO, $this->config->root_dir.'files/certificates/ssl.croinform.cer');
//        curl_setopt($ch, CURLOPT_CAPATH, $this->config->root_dir.'files/certificates/cacer.p7b');
//        curl_setopt($ch, CURLOPT_SSLCERT, $this->config->root_dir.'files/certificates/ssl.croinform.cer'); 
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        if ($result === false)
        {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($errno, $error);echo '</pre><hr />';
        }
        curl_close($ch);
        
echo $result;        
        return $result;
    }
}