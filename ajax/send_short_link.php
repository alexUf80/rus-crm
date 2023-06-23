<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

chdir('..');
require 'autoload.php';

class SendPaymentLinkAjax extends Core
{
    private $response = array();
    
    public function run()
    {
        $short_link = $this->request->post('short_link');

        $phone = $this->request->post('phone');
        $phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);

        if (empty($phone)) {
            $this->response['data'] = 'Ошибка. Нет номера';
            $this->output();
            return;
        } elseif (strlen($phone) != 11) {
            //var_dump($phone);
            //exit;
            $this->response['data'] = 'Ошибка. Неверный формат номера';
            $this->output();
            return;
        }

        //var_dump($order->phone_mobile);
        //exit;
        $action = $this->request->get('action', 'string');    

        switch($action || true):
            
            case 'send':
                
                $this->send_action($phone, $short_link);
                
            break;
            
        endswitch;

        $this->output();
    }

    private function send_action($phone, $short_link)
    {
        //INSERT INTO `s_sms_by_collectors` (`id`, `phone`, `number_of`, `updated_at`) VALUES (NULL, '79276928586', '1', CURRENT_TIMESTAMP);
        //UPDATE `s_sms_by_collectors` SET `number_of`=3 WHERE phone = 79276928586 

        $query = $this->db->placehold("
            SELECT number_of
            FROM __sms_by_collectors
            WHERE phone = ?
            ORDER BY id DESC
            LIMIT 1
        ", $phone);
        $this->db->query($query);
        
        $number_of = $this->db->result('number_of');

        if ($number_of > 100) {
            $this->response['data'] = "Не отправлено. Уже отправлено больше 9 СМС";
            return;
        }
        
        //Срочно погасите просроченную задолженность! Вся информация в личном кабинете: {url} \"Премьер\" 88003331280

        $sms = $this->sms->send($phone, "{$short_link}: Оплата задолженности ООО МКК Русзаймсервис 89190303610");

        $filter['search']['phone'] = $phone;

        $user = $this->users->get_users($filter);
    
        if ($user && isset($user[0])) {
            //var_dump($user);
            //exit;
            
            $this->response['data'] = 'Отправлено. ФИО: ' . $user[0]->lastname. ' ' . $user[0]->firstname. ' ' . $user[0]->patronymic . '. Всего отправлено: '. ($number_of +  1) .' СМС. manager.nalichnoeplus.ru/client/' . $user[0]->id;
            //return;
        } else {
            $this->response['data'] = 'Отправлено. Не найден пользователь с таким номером. Всего отправлено: '. ($number_of +  1) .' СМС';
            //return;
        }
    

        if ($number_of) {
            $query = $this->db->placehold("
                UPDATE __sms_by_collectors SET ?% WHERE phone = ?
            ", ['number_of' => $number_of + 1, 'updated_at' => time()], $phone);
            $this->db->query($query);
        } else {
            $query = $this->db->placehold("
                INSERT INTO __sms_by_collectors SET ?%
            ", ['phone' => $phone, 'number_of' => 1]);
            $this->db->query($query);
        }

        //$this->response['data'] = 'Всего отправлено: '. ($number_of +  1) .' СМС';
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

$sms_code = new SendPaymentLinkAjax();
$sms_code->run();