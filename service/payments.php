<?php



chdir('..');



require 'autoload.php';



class PaymentsService extends Core

{

    private $response = array(

        'info' => array(

            'date' => 'дата оплаты',

            'contract_number' => 'номер договора',

            'operation_number' => 'номер оплаты',

            'amount' => 'сумма оплаты в рублях',

            'client' => 'ФИО клиента',

            'order_id' => 'b2p Номер заказа',

            'operation_id' => 'b2p Номер операции'

        )



    );



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

                o.created,

                o.id,

                o.amount,

                o.type,

                c.number,

                u.lastname,

                u.firstname,

                u.patronymic,

                t.operation as operation_id,

                t.register_id

            FROM __operations AS o

            LEFT JOIN __users AS u

            ON u.id = o.user_id

            LEFT JOIN __contracts AS c

            ON c.id = o.contract_id

            LEFT JOIN __transactions AS t

            ON t.id = o.transaction_id

            WHERE o.type IN ('REPAYMENT_OD', 'REPAYMENT_PERCENT', 'REPAYMENT_PENI', 'REPAYMENT_PERCENT_ADV')

            AND DATE(o.created) >= ?

            AND DATE(o.created) <= ?

            ORDER BY o.id ASC

        ", $date_from, $date_to);

        $this->db->query($query);

        $payments = $this->db->results();



        $this->response['success'] = 1;



        if (!empty($payments))

        {

            $items = [];

            foreach ($payments as $payment)

            {

                if (!isset($items[$payment->number]))

                    $items[$payment->number] = [];



                $payment_date = date('Ymd', strtotime($payment->created));

                if (!isset($items[$payment->number][$payment_date]))

                {

                    $items[$payment->number][$payment_date] = [

                        'contract_number' => $payment->number,

                        'client' => $payment->lastname.' '.$payment->firstname.' '.$payment->patronymic,

                        'date' => $payment->created,

                        'od' => 0,

                        'percent' => 0,

                        'peni' => 0

                    ];

                }



                $items[$payment->number][$payment_date]['payment_id'] = $payment->id;

                if ($payment->type == 'REPAYMENT_OD')

                    $items[$payment->number][$payment_date]['od'] += $payment->amount;

                elseif ($payment->type == 'REPAYMENT_PENI')

                    $items[$payment->number][$payment_date]['peni'] += $payment->amount;

                else

                    $items[$payment->number][$payment_date]['percent'] += $payment->amount;



            }



            $this->response['items'] = [];

            foreach ($items as $contract_items)

                foreach ($contract_items as $contract_item)

                    $this->response['items'][] = $contract_item;

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

new PaymentsService();