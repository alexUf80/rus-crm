<?php

class Fms_scoring extends Core
{
    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    private $api_url = 'http://services.fms.gov.ru/info-service.htm?sid=2000';
    private $cookie_dir = 'files/scorings/cookies/';
    private $captcha_dir = 'files/scorings/captcha/';
    
    private $session_id = null;
    
    private $page = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->coockie_dir = $this->config->root_dir.$this->cookie_dir;
        $this->captcha_dir = $this->config->root_dir.$this->captcha_dir;

        $this->session_id = md5(rand().microtime());
        
    }
    

    public function run_scoring($scoring_id)
    {
        $update = array();
        
    	$scoring_type = $this->scorings->get_type('fms');
        
        if ($scoring = $this->scorings->get_scoring($scoring_id))
        {
            if ($order = $this->orders->get_order((int)$scoring->order_id))
            {
                if (empty($order->passport_serial))
                {
                    $update = array(
                        'status' => 'error',
                        'string_result' => 'в заявке не указаны серия и номер паспорта'
                    );
                }
                else
                {
                    $passport_serial = str_replace(array(' ', '-'), '', $order->passport_serial);
                    $serial = substr($passport_serial, 0, 4);
                    $number = substr($passport_serial, 4, 6);
        
                    $resp = $this->check_passport($serial, $number);
        
                    $pattern = '~<h4 class="ct-h4">(.*?)</h4>~';
                    preg_match($pattern, $resp, $result_string);

                    $score = 0;
                    if (!empty($result_string[1]))
                    {
                        if (stripos($result_string[1], 'Cреди недействительных не значится') === false)
                        {
                            // Первая С может быть или кирилицей или латиницей
                            if (stripos($result_string[1], 'Среди недействительных не значится') !== false)
                                $score = 1;
                        }
                        else
                        {
                            $score = 1;
                        }

                        $update = array(
                            'status' => 'completed',
                            'body' => serialize(array('response' => $result_string[1])),
                            'success' => $score
                        );
                        if ($score)
                            $update['string_result'] = 'Паспорт корректный';
                        else
                            $update['string_result'] = 'Паспорт некорректный';            
                        
                        $this->scorings->update_scoring($scoring_id, $update);
                        
                        $this->soap1c->send_fms(empty($order->id_1c) ? $order->order_id : $order->id_1c , $order->passport_serial, $score);
                        
                        return $update;
                    }
                    else
                    {
                        if ($scoring->repeat_count < 2)
                        {
                            $update = array(
                                'status' => 'repeat',
                                'body' => 'При запросе произошла ошибка',
                                'string_result' => 'ПОВТОРНЫЙ ЗАПРОС',
                                'repeat_count' => $scoring->repeat_count + 1,
                            );
                        }
                        else
                        {                            
                            $update = array(
                                'status' => 'error',
                                'string_result' => 'При запросе произошла ошибка'
                            );
                        }
                    }
                }
                
            }
            else
            {
                $update = array(
                    'status' => 'error',
                    'string_result' => 'не найдена заявка'
                );
            }
            
            if (!empty($update))
                $this->scorings->update_scoring($scoring_id, $update);
            
            return $update;

        }
    }
    


    public function run($audit_id, $user_id, $order_id)
    {
        $this->user_id = $user_id;
        $this->audit_id = $audit_id;
        $this->order_id = $order_id;
        
        $this->type = $this->scorings->get_type('fms');
    	
        $user = $this->users->get_user((int)$user_id);
        
        return $this->scoring($user->passport_serial);
    }

    private function scoring($passport)
    {
        $passport_serial = str_replace('-', '', $passport);
        $serial = substr($passport_serial, 0, 4);
        $number = substr($passport_serial, 4, 6);
        
        $resp = $this->check_passport($serial, $number);
        
        $pattern = '~<h4 class="ct-h4">(.*?)</h4>~';
        preg_match($pattern, $resp, $result_string);
        
        $score = 0;
        if (!empty($result_string[1]))
        {
            if (stripos($result_string[1], 'Cреди недействительных не значится') === false)
            {
                // Первая С может быть или кирилицей или латиницей
                if (stripos($result_string[1], 'Среди недействительных не значится') !== false)
                    $score = 1;
            }
            else
            {
                $score = 1;
            }
    
            $add_scoring = array(
                'user_id' => $this->user_id,
                'audit_id' => $this->audit_id,
                'type' => 'fms',
                'body' => $result_string[1],
                'success' => (int)$score
            );
            if ($score)
            {
                $add_scoring['string_result'] = 'Паспорт корректный';
            }
            else
            {
                $add_scoring['string_result'] = 'Паспорт некорректный';
            }

            $this->scorings->add_scoring($add_scoring);


        }
        
        return $score;
    }

    public function check_passport($serial, $number)
    {
        $this->load_form();
        $this->load_captcha();
        
        $task_id = $this->anticaptcha->create_task($this->captcha_dir.$this->session_id.'.jpg');
echo $task_id;        
        do {
            sleep(1);
            $task_result = $this->anticaptcha->get_task_result($task_id);
        } while(!empty($task_result) && $task_result->status != 'ready' && $task_result->errorId == 0);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($task_result);echo '</pre><hr />';
        if (empty($task_result->errorId))
        {
            $captcha_code = $task_result->solution->text;            
            
            $post_data = array(
                'sid' => '2000',
                'form_name' => 'form',
                'DOC_SERIE' => $serial,
                'DOC_NUMBER' => $number,
                'captcha-input' => $captcha_code,
            );
            
            $resp = $this->send_form($post_data);

            return $resp;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($resp);echo '</pre><hr />';            
            
        }
        
    }
    
    private function load_form()
    {

        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
//            'Accept-Encoding: gzip, deflate',
            'Referer: http://services.fms.gov.ru/info-service-result.htm?sid=2000',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );


        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');        
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        
        $this->page = $result;
    }
    
    private function load_captcha()
    {
        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: image/webp,*/*',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: http://services.fms.gov.ru/info-service-result.htm?sid=2000',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );
        
        $captcha_url = 'http://services.fms.gov.ru/services/captcha.jpg';
        $ch = curl_init($captcha_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');        
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        $captcha_src = $result;

        file_put_contents($this->captcha_dir.$this->session_id.'.jpg', $captcha_src);
    }    
    
    private function send_form($data)
    {
        $headers = array(
            'Host: services.fms.gov.ru',
            'User-Agent: Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
//            'Accept-Encoding: gzip, deflate',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: http://services.fms.gov.ru',
            'DNT: 1',
            'Connection: keep-alive',
            'Referer: http://services.fms.gov.ru/info-service.htm?sid=2000',
            'Upgrade-Insecure-Requests: 1',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        );

        $url = 'http://services.fms.gov.ru/info-service.htm';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        
//        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_REFERER, 'http://services.fms.gov.ru/info-service.htm?sid=2000');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

//echo __FILE__.' '.__LINE__.'<br /><pre><code>';var_dump($result);echo '</pre><hr />';
//    $this->send('http://services.fms.gov.ru/info-service-result.htm?sid=2000');



        return $result;
    }
    
    public function send($url, $data = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_dir.$this->session_id.'.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_dir.$this->session_id.'.txt');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!is_null($data))
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
echo $result;                
        return $result;
    }
}