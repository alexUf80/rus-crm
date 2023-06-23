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

        $url_lin = $_SERVER['HTTP_REFERER'];

        $this->response = '<html>
        <head>
        <meta http-equiv="refresh" content="5;URL='.$url_lin.'"/>
        </head>
        <body>
        Успешно отправлено<br>
        <a href="javascript:history.back()" title="Вернуться на предыдущую страницу" > Вернуться на предыдущую страницу </a>
        </body>';
    }


    private function output()
    {
        echo $this->response;
    }
}

$sms_code = new SendPaymentLinkAjax();
$sms_code->run();