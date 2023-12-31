<?php
chdir('..');
require 'autoload.php';

class payments extends Core
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

        $payments = OperationsORM::whereIn('type', ['PAY', 'RECURRENT'])->whereBetween('created', [$date_from, $date_to])->get();

        $this->response['success'] = 1;

        if (!empty($payments))
        {
            $this->response['items'] = array();

            foreach ($payments as $payment)
            {
                $contract = ContractsORM::find($payment->contract_id);

                if(empty($contract))
                    continue;

                $transaction = TransactionsORM::find($payment->transaction_id);

                $item = new StdClass();
                $item->Date = date('Ymd000000', strtotime($payment->created));
                $item->Contract_number = (string)$contract->number;
                $item->Od = (float)$transaction->loan_body_summ;
                $item->Percent = (float)$transaction->loan_percents_summ;
                $item->Peni = (float)$transaction->loan_peni_summ;

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

new payments();