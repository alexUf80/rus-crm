<?php
chdir('..');
require 'autoload.php';

class ContractsService extends Core

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

        if (empty($date_from) || empty($date_to))
        {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите даты в формате yyyy-mm-dd';
            $this->output();
        }

        $password = $this->request->get('password');

        if ($password != $this->password)
        {
            $this->response['error'] = 1;
            $this->response['message'] = 'Укажите пароль обмена';
            $this->output();
        }

        $query = $this->db->placehold("

            SELECT 
                c.id,
                c.number,
                c.inssuance_date,
                c.amount,
                c.service_insurance,
                c.service_sms,
                u.lastname,
                u.firstname,
                u.patronymic
            FROM __contracts AS c
            LEFT JOIN __users AS u
            ON u.id = c.user_id
            WHERE DATE(c.inssuance_date) >= ?
            AND DATE(c.inssuance_date) <= ?
            # ТЕСТОВЫЕ КОНТРАКТЫ
            AND c.number NOT IN ('1016-6080')
        ", $date_from, $date_to);
        $this->db->query($query);
        $contracts = $this->db->results();


        $this->response['success'] = 1;

        if (!empty($contracts))
        {
            $this->response['items'] = array();
            foreach ($contracts as $contract)
            {

                // if ($contract->service_insurance == 1)
                // {
                //     $insurance = OperationsORM::where(['contract_id' => $contract->id, 'type' => 'INSURANCE'])->get()->first();
                //     if (!empty($insurance->amount))
                //         $contract->amount += $insurance->amount;
                    
                // }

                // if ($contract->service_sms == 1)
                //     $contract->amount += 149;

                $item = new StdClass();
                $item->number = $contract->number;
                $item->date = $contract->inssuance_date;
                $item->client = $contract->lastname.' '.$contract->firstname.' '.$contract->patronymic;
                $item->amount = $contract->amount;
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
new ContractsService();