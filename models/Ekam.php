<?php


class Ekam extends Core
{
    private $token;
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->token = '95b8009ab7160480497399f00b061de986064679195db4379a7f9de0d2cc';
        //токен новой кассы, пока только для страховок
        $this->new_token = '5fd8c44d1299195abf1a64cad7117f49945dc4d9aa61f050409b59698769';
    }

    public function send_insurance($operation_id)
    {
        if ($operation = $this->operations->get_operation($operation_id))
        {
            $insurance = $this->insurances->get_operation_insurance($operation->id);
            $user = $this->users->get_user($operation->user_id);
            //$contract = $this->contracts->get_contract($operation->contract_id);

            $receipt = [
                'amount' => $operation->amount,
                'title' => 'Оплата за Страховой полис',
                'operation_id' => $operation_id,
            ];

            if (!empty($insurance->protection))
            {
                $receipt['email'] = 'str@nalichnoeplus.ru';
            }
            else
            {
                if (!empty($user->email))
                    $receipt['email'] = $user->email;
                if (!empty($user->phone_mobile))
                    $receipt['phone_mobile'] = $user->phone_mobile;                
            }

            //новая касса для страховок
            $this->token = $this->new_token;

            return $this->send_receipt($receipt);
        }
        else
        {
            return 'undefined_operation';
        }
    }

    public function send_receipt($receipt)
    {
        $title = $receipt['title'];
        $operation_id = $receipt['operation_id'];
        $amount = $receipt['amount'];
        $email = $receipt['email'];
        $phone_mobile = $receipt['phone_mobile'];


        /*
            1 	Продажа товара, за исключением подакцизного товара 	ТОВАР
            2 	Продажа подакцизного товара 	ПОДАКЦИЗНЫЙ ТОВАР
            3 	Выполняемая работа 	РАБОТА
            4 	Оказываемая услуга 	УСЛУГА
            5 	Прием ставок при осуществлении деятельности по организации и проведению азартных игр 	СТАВКА АЗАРТНОЙ ИГРЫ
            6 	Выплат денежных средств в виде выигрыша при осуществлении деятельности по организации и проведению азартных игр 	ВЫИГРЫШ АЗАРТНОЙ ИГРЫ
            7 	Приеме денежных средств при реализации лотерейных билетов, приеме лотерейных ставок при осуществлении деятельности по организации и проведению лотерей 	ЛОТЕРЕЙНЫЙ БИЛЕТ
            8 	Вплата денежных средств в виде выигрыша при осуществлении деятельности по организации и проведению лотерей 	ВЫИГРЫШ ЛОТЕРЕИ
            9 	Предоставление прав на использование результатов интеллектуальной деятельности или средств индивидуализации 	ПРЕДОСТАВЛЕНИЕ РИД
            10 	Аванс, задаток, предоплата, кредит, взнос в счет оплаты, пеня, штраф, вознаграждение, бонус и иной аналогичный предмет расчета 	ПЛАТЕЖ / ВЫПЛАТА
            11 	Вознаграждение пользователя, являющегося платежным агентом (субагентом), банковским платежным агентом (субагентом), комиссионером, поверенным или иным агентом 	СОСТАВНОЙ ПРЕДМЕТ РАСЧ.
            12 	Предмет расчета, состоящем из предметов, каждому из которых может быть присвоено значение от «0» до «11» 	АГЕНТСКОЕ ВОЗНАГРАЖДЕНИЕ
            13 	Предмет расчета, не относящемуся к предметам расчета, которым может быть присвоено значение от «0» до «12» 	ИНОЙ ПРЕДМЕТ РАСЧЕТА 
        */
        if (isset($receipt['fiscal_product_type'])) {
            $fiscal_product_type = $receipt['fiscal_product_type'];
        } else {
            $fiscal_product_type = 4;
        }

        $item = [
            'quantity' => 1,
            'title' => $title,
            'total_price' => $amount,
            'vat_rate' => 20,
            'fiscal_product_type' => $fiscal_product_type
        ];

        $total_amount = $amount;

        $data = [
            'type' => 'SaleReceiptRequest',
            'counter_offer_amount' => $total_amount,
            'lines' =>
            [
                0 => $item,
            ],
            'order_id' => $operation_id,
            'tax_system' => '1',
            //'should_print' => true
        ];

        if (!empty($email))
            $data['email'] = $email;
        if (!empty($phone_mobile))
            $data['phone_number'] = $phone_mobile;

        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://app.ekam.ru/api/online/v2/receipt_requests',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'X-Access-Token: '.$this->token.'',
            'content-type: application/json'
        ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);

        $data = '-----------------'.PHP_EOL.json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.$res.PHP_EOL.PHP_EOL;
        file_put_contents('files/ekam.log', $data, FILE_APPEND);

        //$this->logging(__METHOD__, 'https://app.ekam.ru/api/online/v2/receipt_requests', (array)$data, (array)$res, 'ekam.log');

        return $res;
    }

    public function return_receipt_request($receipt)
    {
        $title = $receipt['title'];
        $order_id = $receipt['order_id'];
        $amount = $receipt['amount'];
        $email = $receipt['email'];

        /*
            1 	Продажа товара, за исключением подакцизного товара 	ТОВАР
            2 	Продажа подакцизного товара 	ПОДАКЦИЗНЫЙ ТОВАР
            3 	Выполняемая работа 	РАБОТА
            4 	Оказываемая услуга 	УСЛУГА
            5 	Прием ставок при осуществлении деятельности по организации и проведению азартных игр 	СТАВКА АЗАРТНОЙ ИГРЫ
            6 	Выплат денежных средств в виде выигрыша при осуществлении деятельности по организации и проведению азартных игр 	ВЫИГРЫШ АЗАРТНОЙ ИГРЫ
            7 	Приеме денежных средств при реализации лотерейных билетов, приеме лотерейных ставок при осуществлении деятельности по организации и проведению лотерей 	ЛОТЕРЕЙНЫЙ БИЛЕТ
            8 	Вплата денежных средств в виде выигрыша при осуществлении деятельности по организации и проведению лотерей 	ВЫИГРЫШ ЛОТЕРЕИ
            9 	Предоставление прав на использование результатов интеллектуальной деятельности или средств индивидуализации 	ПРЕДОСТАВЛЕНИЕ РИД
            10 	Аванс, задаток, предоплата, кредит, взнос в счет оплаты, пеня, штраф, вознаграждение, бонус и иной аналогичный предмет расчета 	ПЛАТЕЖ / ВЫПЛАТА
            11 	Вознаграждение пользователя, являющегося платежным агентом (субагентом), банковским платежным агентом (субагентом), комиссионером, поверенным или иным агентом 	СОСТАВНОЙ ПРЕДМЕТ РАСЧ.
            12 	Предмет расчета, состоящем из предметов, каждому из которых может быть присвоено значение от «0» до «11» 	АГЕНТСКОЕ ВОЗНАГРАЖДЕНИЕ
            13 	Предмет расчета, не относящемуся к предметам расчета, которым может быть присвоено значение от «0» до «12» 	ИНОЙ ПРЕДМЕТ РАСЧЕТА
        */
        if (isset($receipt['fiscal_product_type'])) {
            $fiscal_product_type = $receipt['fiscal_product_type'];
        } else {
            $fiscal_product_type = 4;
        }

        $item = [
            'quantity' => 1,
            'title' => $title,
            'total_price' => $amount,
            'vat_rate' => 20,
            'fiscal_product_type' => $fiscal_product_type
        ];

        $total_amount = $amount;

        $data = [
            'type' => 'ReturnReceiptRequest',
            'counter_offer_amount' => $total_amount,
            'lines' =>
                [
                    0 => $item,
                ],
            'order_id' => $order_id,
            'tax_system' => '1',
            //'should_print' => true
        ];

        if (!empty($email))
            $data['email'] = $email;


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.ekam.ru/api/online/v2/receipt_requests',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'X-Access-Token: '.$this->token.'',
                'content-type: application/json'
            ),
        ));

        $res = curl_exec($curl);

        curl_close($curl);

        $data = '-----------------'.PHP_EOL.json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL.$res.PHP_EOL.PHP_EOL;
        file_put_contents('files/ekam.log', $data, FILE_APPEND);

        //$this->logging(__METHOD__, 'https://app.ekam.ru/api/online/v2/receipt_requests', (array)$data, (array)$res, 'ekam.log');

        return $res;

    }
    
    public function send_reject_reason($order_id)
    {
    	if ($order = $this->orders->get_order($order_id))
        {  
            $receipt = [
                'amount' => 39,
                'title' => 'Информирование о причине отказа',
                'operation_id' => $order->order_id,
            ];

            if (!empty($order->email))
                $receipt['email'] = $order->email;
            if (!empty($order->phone_mobile))
                $receipt['phone_mobile'] = $order->phone_mobile;                

            
            return $this->send_receipt($receipt);
        }
        else
        {
            return 'undefined order';
        }
    }        

    public function send_bud_v_kurse($order_id)
    {
        if ($order = $this->orders->get_order($order_id)) {
            $receipt = [
                'amount' => 199,
                'title' => 'Услуга "Будь в курсе"',
                'operation_id' => $order->order_id,
            ];

            if (!empty($order->email))
                $receipt['email'] = $order->email;
            if (!empty($order->phone_mobile))
                $receipt['phone_mobile'] = $order->phone_mobile;


            return $this->send_receipt($receipt);
        } else {
            return 'undefined order';
        }
    }

    public function send_return_bud_v_kurse($operation_id)
    {
        if ($operation = $this->operations->get_operation($operation_id)) {
            $user = $this->users->get_user($operation->user_id);
            $receipt = [
                'amount' => 199,
                'title' => 'Услуга "Будь в курсе"',
                'operation_id' => $operation->id,
            ];

            if (!empty($user->email))
                $receipt['email'] = $user->email;


            return $this->return_receipt_request($receipt);
        } else {
            return 'undefined order';
        }
    }

    public function send_return_insure($operation_id)
    {
        if ($operation = $this->operations->get_operation($operation_id)) {
            $user = $this->users->get_user($operation->user_id);

            $receipt = [
                'amount' => $operation->amount,
                'title' => 'Оплата за Страховой полис',
                'operation_id' => $operation_id,
            ];
                if (!empty($user->email))
                    $receipt['email'] = $user->email;

            //новая касса для страховок
            $this->token = $this->new_token;

            return $this->return_receipt_request($receipt);
        }
        else
        {
            return 'undefined_operation';
        }
    }

    public function send_return_reject_reason($operation_id)
    {
        if ($operation = $this->operations->get_operation($operation_id)) {
            $user = $this->users->get_user($operation->user_id);

            $receipt = [
                'amount' => 39,
                'title' => 'Информирование о причине отказа',
                'operation_id' => $operation->id,
            ];
            if (!empty($user->email))
                $receipt['email'] = $user->email;


            return $this->return_receipt_request($receipt);
        } else {
            return 'undefined order';
        }
    }
}