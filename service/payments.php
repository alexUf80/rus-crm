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
                o.transaction_id,
                o.amount,
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
            WHERE 
                o.type = 'PAY'
                AND DATE(o.created) >= ?
                AND DATE(o.created) <= ?
        ", $date_from, $date_to);
        $this->db->query($query);
        $payments = $this->db->results();
        
        $this->response['success'] = 1;
        
        if (!empty($payments))
        {
            $this->response['items'] = array();
            foreach ($payments as $payment)
            {
                
                $item = new StdClass();
                $item->date = $payment->created;
                $item->contract_number = $payment->number;
                $item->operation_number = date('md', strtotime($payment->created)).'-'.$payment->transaction_id;
                $item->amount = $payment->amount;
                $item->client = $payment->lastname.' '.$payment->firstname.' '.$payment->patronymic;
                $item->order_id = $payment->register_id;
                $item->operation_id = $payment->operation_id;
                
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
new PaymentsService();