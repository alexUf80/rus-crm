<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

chdir('..');
require 'autoload.php';

class SmsCode extends Core
{
    // задержка между отправкой смс
    private $delay = 30;
    
    private $response = array();
    
    public function run()
    {
        $phone = $this->request->get('phone', 'string');

        $action = $this->request->get('action', 'string');        
        switch($action):
            
            case 'send':
                
                $this->send_action($phone);
                
            break;
            
            case 'send_accept_code':
                
                $contract_id = $this->request->get('contract_id', 'integer');
                if ($contract = $this->contracts->get_contract($contract_id))
                {
                    $accept_code = rand(1000, 9999);
                    $this->contracts->update_contract($contract->id, array('accept_code'=>$accept_code));

                    $order = $this->orders->get_order((int)$contract->order_id);
                    $msg = 'Активируй займ '.($order->amount*1).' в личном кабинете, код'. $accept_code.' https://ecozaym24.ru/lk/login';

                    if (0 && !empty($this->is_developer))
                    {
                        $this->response['mode'] = 'developer';
                        $this->response['developer_code'] = $accept_code;
                        
                        $sms_message['response'] = 'DEVELOPER MODE';
                    }
                    else
                    {                                
                        $send_response = $this->sms->send($order->phone_mobile, $msg);
                        $this->response['response'] = $send_response;
                        $sms_message['response'] = $send_response;
                        
                        $this->response['mode'] = 'ADMIN MODE '.$this->manager->id;
                    
                        $this->response['success'] = true;
                    }
                }
                else
                {
                    $this->response['error'] = 'contract not found';            
                }

            break;
            
            case 'check':
                
                $code = $this->request->get('code', 'string');
                
                $this->check_action($phone, $code);
                
            break;
            
        endswitch;

        $this->output();
    }

    private function send_action($phone)
    {
        if (!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + $this->delay) > time())
        {
            $this->response['error'] = 'sms_time';
            $this->response['time_left'] = $_SESSION['sms_time'] + $this->delay - time();
        }
        else
        {
            $rand_code = mt_rand(1000, 9999);

            $sms_message = array(
                'code' => $rand_code,
                'phone' => $phone,
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                'created' => date('Y-m-d H:i:s'),                                
            );
                                    
            if (!empty($this->is_developer))
            {
                $this->response['mode'] = 'developer';
                $this->response['developer_code'] = $rand_code;
                
                $sms_message['response'] = 'DEVELOPER MODE';
            }
            else
            {                                
                $send_response = $this->sms->send($phone, $rand_code);
                $this->response['response'] = $send_response;
                $sms_message['response'] = $send_response;
                
                $this->response['mode'] = 'ADMIN MODE '.$this->manager->id;
            }
            
            $this->sms->add_message($sms_message);
            
            $_SESSION['sms_time'] = time();

            $this->response['success'] = true;
            if (empty($_SESSION['sms_time']))
                $this->response['time_left'] = 0;
            else
                $this->response['time_left'] = ($_SESSION['sms_time'] + $this->delay) - time();
        }
    }
    
    private function check_action($phone, $code)
    {
        if ($db_code = $this->sms->get_code($phone))
        {
            $this->response['success'] = intval($db_code == $code);
            
        }
        else
        {
            $this->response['success'] = 0;
        }
    }
    
    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");		
        
        echo json_encode($this->response);
    }
}

$sms_code = new SmsCode();
$sms_code->run();