<?php

chdir('..');
require 'autoload.php';

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
        $date = $this->request->get('date');

        $date_from = date('Y-m-d 00:00:00', strtotime($date));
        $date_to = date('Y-m-d 23:59:59', strtotime($date));

        if (empty($date)) {
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

        $taxings = OperationsORM::whereIn('type', ['PENI', 'PERCENTS'])->whereBetween('created', [$date_from, $date_to])->get();

        if (empty($taxings)) {
            $this->response['success'] = 1;
            $this->response['message'] = 'Оплат нет';
            $this->output();
        }

        $operations = array();

        foreach ($taxings as $taxing) {

            if (isset($contracts[$taxing->contract_id])) {

                $operations[$taxing->contract_id] =
                    [
                        'od'   => max($operations[$taxing->contract_id]['od'], $taxing->loan_body_summ),
                        'prc'  => max($operations[$taxing->contract_id]['prc'], $taxing->loan_percents_summ),
                        'peni' => max($operations[$taxing->contract_id]['peni'], $taxing->loan_peni_summ)
                    ];

            } else {

                $operations[$taxing->contract_id] =
                    [
                        'od'   => $taxing->loan_body_summ,
                        'prc'  => $taxing->loan_percents_summ,
                        'peni' => $taxing->loan_peni_summ
                    ];
            }

        }

        $this->response['items'] = array();

        foreach ($operations as $contractId => $operation) {
            $contract = ContractsORM::find($contractId);

            $item = new StdClass();
            $item->НомерДоговора = (string)$contract->number;
            $item->ОстатокОД = $operation['od'];
            $item->ОстатокПроцентов = $operation['prc'];
            $item->ОстатокПени = $operation['peni'];

            $this->response['items'][] = $item;
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