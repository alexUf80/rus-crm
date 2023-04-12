<?php

class Balances extends Core
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

        $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
        $date_to = date('Y-m-d 23:59:59', strtotime($date_to));

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

        $contracts = ContractsORM::whereIn('status', [2,4])->get();

        $this->response['success'] = 1;

        if (!empty($contracts))
        {
            $this->response['items'] = array();

            foreach ($contracts as $contract)
            {
                $item = new StdClass();
                $item->НомерДоговора    = (string)$contract->number;
                $item->ОстатокОД        = $contract->loan_body_summ;
                $item->ОстатокПроцентов = $contract->loan_percents_summ;
                $item->ОстатокПени      = $contract->loan_peni_summ;

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

new Balances();