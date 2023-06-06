<?php
error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

chdir('..');
require 'autoload.php';

class SendPaymentLinkAjax extends Core
{
    private $response = '';

    public function run()
    {
        $short_link = $this->request->post('short_link');
        $phone = $this->request->post('phone');
        $userId = $this->request->post('userId');

        if (empty($phone)) {
            $this->response = 'Ошибка. Нет номера';
            $this->output();
            return;
        } elseif (strlen($phone) != 11) {
            $this->response = 'Ошибка. Неверный формат номера';
            $this->output();
            return;
        }

        $action = $this->request->get('action', 'string');

        switch($action || true):

            case 'send':

                $this->send_action($phone, $short_link, $userId);

                break;

        endswitch;

        $this->output();
    }

    private function send_action($phone, $short_link, $userId)
    {
        $link = 'https://'.$short_link;
        $msg = "Ваша ссылка для оплаты задолженности : ". $link;

        $sms = $this->sms->send($phone, $msg);

        $insert =
            [
                'code' => '0',
                'message'  => $msg,
                'phone'    => $phone,
                'response' => $sms,
                'user_id'  => $userId
            ];

        // SmsMessagesORM::insert($insert);
        $this->sms->add_message($insert);

        $this->response = 'Успешно отправлено';
    }


    private function output()
    {
        echo $this->response;
    }
}

$sms_code = new SendPaymentLinkAjax();
$sms_code->run();