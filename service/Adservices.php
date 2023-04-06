<?php
chdir('..');
require 'autoload.php';

class Adservices extends Core
{
    private $response = array();
    private $password = 'AX6878EK';

    public function __construct()
    {
        $this->run();
    }

    private function run()
    {
        $date_from = $this->request->get('from');
        $date_to = $this->request->get('to');

        if (empty($date_from) || empty($date_to)) {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите даты в формате yyyy-mm-dd';
            $this->output();
        }

        $password = $this->request->get('password');

        if ($password != $this->password) {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите пароль обмена';
            $this->output();
        }

        $types =
            [
                'REJECT_REASON',
                'INSURANCE',
                'SMS',
                'INSURANCE_BC'
            ];

        $adservices = OperationsORM::whereIn('type', $types)->whereBetween('created', [$date_from, $date_to])->get();

        $this->response['success'] = 1;

        if (!empty($adservices))
        {
            $this->response['items'] = array();

            foreach ($adservices as $service)
            {
                $item = new StdClass();
                $item->Операция_ID = $service->id;
                $item->Клиент_ID = $service->user_id;
                $item->Дата = date('Ymd000000', strtotime($service->created));
                $item->Страховка = (in_array($service->type, ['INSURANCE_BC', 'INSURANCE'])) ? 1 : 0;
                $item->Смс_Информирование = ($service->type == 'SMS') ? 1 : 0;
                $item->Причина_отказа = ($service->type == 'REJECT_REASON') ? 1 : 0;
                $this->response['items'][] = $item;
            }
        }
        $this->output();
    }

    private function output()
    {
        header('Content-type:application/json');
        echo json_encode($this->response);
        exit;
    }
}

new Adservices();