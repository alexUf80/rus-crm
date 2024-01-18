<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('default_socket_timeout', '300');

class Onec implements ApiInterface
{
    protected static $link = "http://79.137.210.6/corp_ruszaymserv/ws/";
    protected static $login = 'admin';
    protected static $password = '2020';
    protected static $orderId;

    public static function sendRequest($params)
    {
        return self::{$params['method']}($params['params']);
    }

    public static function send_loan($order_id)
    {
        self::$orderId = $order_id;

        $order = OrdersORM::find($order_id);
        $contract = ContractsORM::find($order->contract_id);
        $user = UsersORM::find($order->user_id);

        if (empty($contract->inssuance_date))
            return 3;

        $p2pcredit = P2pOperationORM::whereRaw('contract_id = ? and status = "APPROVED"', [$contract->id])->get()->first();

        $user->regaddress = AdressesORM::find($user->regaddress_id);
        $user->faktaddress = AdressesORM::find($user->faktaddress_id);

        $passport_serial = str_replace([' ', '-'], '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);

        $card = CardsORM::find($contract->card_id);

        if (!empty($card))
            $cardPan = $card->pan;
        else
            $cardPan = '';

        $item = new StdClass();

        // if ($contract->service_insurance == 1)
        //     $contract->amount += $contract->amount * 0.1;

        // if ($contract->service_sms == 1)
        //     $contract->amount += 149;

        $item->ID = (string)$contract->id;
        $item->НомерДоговора = $contract->number;
        $item->Дата = date('Ymd000000', strtotime($contract->inssuance_date));
        $item->Срок = $contract->period;
        $item->Периодичность = 'День';
        $item->ПроцентнаяСтавка = $contract->base_percent;
        $item->ПСК = '365';
        $item->ПДН = $user->pdn * 100;
        $item->УИДСделки = $contract->uid;
        $item->ИдентификаторФормыВыдачи = $p2pcredit->id > 1410 ? 'ПСБ' : 'ТекущийСчетРасчетов'; 
        $item->ИдентификаторФормыОплаты = 'ТретьеЛицо';
        $item->Сумма = $contract->amount;
        $item->Порог = '1.3';
        $item->ИННОрганизации = '9717088848';
        $item->СпособПодачиЗаявления = 'Дистанционный';
        $item->НомерКарты = $cardPan;
        $item->OrderID = $p2pcredit->register_id;
        $item->OperationID = $p2pcredit->operation_id;

        $item->ГрафикПлатежей = [];

        $client = new StdClass();
        $client->id = $user->id;
        $client->ФИО = $user->lastname . ' ' . $user->firstname . ' ' . $user->patronymic;
        $client->Фамилия = $user->lastname;
        $client->Имя = $user->firstname;
        $client->Отчество = $user->patronymic;
        $client->ДатаРождения = date('Ymd000000', strtotime($user->birth));
        $client->МестоРождения = $user->birth_place;
        $client->АдресРегистрации = $user->regaddress->adressfull;
        $client->АдресПроживания = $user->faktaddress->adressfull;
        $client->Телефон = self::format_phone($user->phone_mobile);
        $client->ИНН = $user->inn;
        $client->СНИЛС = $user->snils;
        $client->Email = $user->email;
        $client->ОКАТО = $user->regaddress->okato;
        $client->ОКТМО = $user->regaddress->oktmo;

        $passport = new StdClass();
        $passport->Серия = $passport_series;
        $passport->Номер = $passport_number;
        $passport->КемВыдан = $user->passport_issued;
        $passport->КодПодразделения = $user->subdivision_code;
        $passport->ДатаВыдачи = date('Ymd000000', strtotime($user->passport_date));

        $client->Паспорт = $passport;

        $item->Клиент = $client;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);

        $response = self::send_request('CRM_WebService', 'Loans', $request);
        $result = json_decode($response);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result, $item);echo '</pre><hr />';
        if (isset($result->return) && $result->return == 'OK')
        {
            $update = [
                'sent_status' => 2,
                'sent_date' => date('Y-m-d H:i:s')
            ];
            ContractsORM::where('id', $contract->id)->update($update);

            return 1;
        }
        else
        {
            return 2;
        }
    }

    private static function send_request($service, $method, $request)
    {
        $params = array();
        if (!empty(self::$login) || !empty(self::$password)) {
            $params['login'] = self::$login;
            $params['password'] = self::$password;
        }

        try {
            $service_url = self::$link . $service . ".1cws?wsdl";

            $client = new SoapClient($service_url, $params);
            $response = $client->__soapCall($method, array($request));
        } catch (Exception $fault) {
            var_dump($fault);
            $response = $fault;
        }

        $response = json_encode($response, JSON_UNESCAPED_UNICODE);

        $insert =
            [
                'orderId' => self::$orderId,
                'request' => json_encode(json_decode($request->TextJSON), JSON_UNESCAPED_UNICODE),
                'response' => $response
            ];

        OnecLogs::insert($insert);

        return $response;
    }

    private static function format_phone($phone)
    {
        if (empty($phone)) {
            return '';
        }

        if ($phone == 'не указан' || $phone == 'не указана') {
            return '';
        }

        $replace_params = array('(', ')', ' ', '-', '+');
        $clear_phone = str_replace($replace_params, '', $phone);

        $substr_phone = mb_substr($clear_phone, -10, 10, 'utf8');
        $format_phone = '7(' . mb_substr($substr_phone, 0, 3, 'utf8') . ')' . mb_substr($substr_phone, 3, 3, 'utf8') . '-' . mb_substr($substr_phone, 6, 2, 'utf8') . '-' . mb_substr($substr_phone, 8, 2, 'utf8');

        return $format_phone;
    }

    private static function send_services($service)
    {
        $item = new StdClass();
        $item->Дата = date('Ymd000000', strtotime($service->date));
        $item->Клиент_id = (string)$service->user_id;
        $item->Сумма = $service->insurance_cost;
        $item->НомерДоговора = (string)$service->number;
        $item->Операция_id = (string)$service->crm_operation_id;
        $item->Страховка = $service->is_insurance;
        $item->OrderID = $service->order_id;
        $item->OperationID = $service->operation_id;
        $item->НомерКарты = $service->card_pan;

        self::$orderId = $service->order_id;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);

        $result = self::send_request('CRM_WebService', 'SaleService', $request);

        if (isset($result->return) && $result->return == 'OK')
            return 1;
        else
            return 2;
    }

    private static function sendTaxing($orderId)
    {
        $start = date('Y-m-d 00:00:00', strtotime('2023-02-01'));
        $end = date('Y-m-d 23:59:59', strtotime('2023-03-31'));

        $percents = OperationsORM::where('type', 'PERCENTS')
            ->whereBetween('created', [$start, $end])
            ->groupBy()
            ->get();

        $groupsOperations = [];


        foreach ($percents as $percent) {
            $date = date('Y-m-d', strtotime($percent->created));
            $groupsOperations[$date][] = $percent;
        }

        foreach ($groupsOperations as $date => $operations) {

            $item = [];

            foreach ($operations as $operation) {
                $contract = ContractsORM::find($operation->contract_id);

                if (empty($contract) || empty($contract->number))
                    continue;

                $item[] =
                    [
                        'НомерДоговора' => $contract->number,
                        'ВидНачисления' => 'Проценты',
                        'ДатаПлатежа' => date('Ymd000000', strtotime($contract->return_date)),
                        'Сумма' => $operation->amount
                    ];
            }

            self::$orderId = 123;

            $request = new StdClass();
            $request->TextJSON = json_encode($item);
            $request->Date = date('YmdHis', strtotime($date));
            $request->INN = '7801323165';

            self::send_request('CRM_WebService', 'InterestCalculation', $request);
        }

        return 1;
    }

    private static function sendPayments($payment)
    {
        $contract = ContractsORM::find($payment->contract_id);

        if(empty($contract))
            return 1;

        $item = new StdClass();
        $item->ID = $payment->id;
        $item->Дата = date('YmdHis', strtotime($payment->date));
        $item->ЗаймID = (string)$payment->order_id;
        $item->Пролонгация = $payment->prolongation;
        $item->Закрытие = $payment->closed;
        $item->СрокПролонгации = $payment->prolongationTerm;
        $item->ИдентификаторФормыОплаты = 'ТретьеЛицо';
        $item->Оплаты =
            [
                [
                    'ИдентификаторВидаНачисления' => 'ОсновнойДолг',
                    'Сумма' => $payment->od
                ],
                [
                    'ИдентификаторВидаНачисления' => 'Проценты',
                    'Сумма' => $payment->prc
                ],
                [
                    'ИдентификаторВидаНачисления' => 'Пени',
                    'Сумма' => $payment->peni
                ]
            ];

        self::$orderId = $payment->order_id;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);

        $result = self::send_request('CRM_WebService', 'Payments', $request);

        if (isset($result->return) && $result->return == 'OK')
            return 1;
        else
            return 2;
    }




    /**
     * Onec::sendTaxingWithPeni()
     * 
     * @param string $date - дата (Y-m-d) за которую нужно отправить начисления 
     * @return void
     */
    public static function sendTaxingWithPeni($date)
    {
        $start = date('Y-m-d 00:00:00', strtotime($date));
        $end = date('Y-m-d 23:59:59', strtotime($date));

        $percents = OperationsORM::whereIn('type', ['PERCENTS', 'PENI'])
            ->whereBetween('created', [$start, $end])
            ->groupBy()
            ->get();

        $groupsOperations = [];

        foreach ($percents as $percent) {
            $date = date('Y-m-d', strtotime($percent->created));
            $groupsOperations[$date][] = $percent;
        }

        foreach ($groupsOperations as $date => $operations) {

            $item = [];

            foreach ($operations as $operation) {
                $contract = ContractsORM::find($operation->contract_id);

                if (empty($contract) || empty($contract->number))
                    continue;

                if ($operation->type == 'PENI')
                {
                    $item[] =
                        [
                            'НомерДоговора' => $contract->number,
                            'ВидНачисления' => 'Пени',
                            'ДатаПлатежа' => date('0001010101'),
                            'Сумма' => $operation->amount
                        ];
                    
                }
                else
                {
                    $item[] =
                        [
                            'НомерДоговора' => $contract->number,
                            'ВидНачисления' => 'Проценты',
                            'ДатаПлатежа' => date('Ymd000000', strtotime($contract->return_date)),
                            'Сумма' => $operation->amount
                        ];
                    
                }
            }

            self::$orderId = 123;

            $request = new StdClass();
            $request->TextJSON = json_encode($item);
            $request->Date = date('YmdHis', strtotime($date));
            $request->INN = '7801323165';

            $response = self::send_request('CRM_WebService', 'InterestCalculation', $request);
            $result = json_decode($response);
            
            if (isset($result->return) && $result->return == 'ОК')
            {
                $update = array(
                    'sent_date' => date('Y-m-d H:i:s'),
                    'sent_status' => 2
                );
            }
            else
            {
                $update = array(
                    'sent_date' => date('Y-m-d H:i:s'),
                    'sent_status' => 8
                );                    
            }
            
            foreach ($operations as $operation)
            {
                OperationsORM::where('id', $operation->id)->update($update);
            }

echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';        
        }
    }

    /**
     * Onec::sendPayment()
     * 
     * @param mixed $payment
     * @return
     */
    public static function sendPayment($payment)
    {
        $item = new StdClass();
        
        $item->ID = $payment->id;
        $item->Дата = date('YmdHis', strtotime($payment->created));
        $item->ЗаймID = (string)$payment->contract_id;
        $item->Пролонгация = empty($payment->prolongation) ? 0 : 1;
        $item->СрокПролонгации = empty($payment->prolongation) ? 0 : 30;
        $item->ИдентификаторФормыОплаты = 'ТретьеЛицо';
        $item->OrderID = $payment->register_id;
        $item->OperationID = $payment->operation;
        
        $item->Закрытие = 0;
        if (!empty($payment->close_date))
            if (strtotime(date('Y-m-d', strtotime($payment->created))) == strtotime(date('Y-m-d', strtotime($payment->close_date))))
                $item->Закрытие = 1;

        $item->Оплаты =
            [
                [
                    'ИдентификаторВидаНачисления' => 'ОсновнойДолг',
                    'Сумма' => empty($payment->loan_body_summ) ? 0 : (float)$payment->loan_body_summ ?? $payment->amount
                ],
                [
                    'ИдентификаторВидаНачисления' => 'Проценты',
                    'Сумма' => empty($payment->loan_percents_summ) ? 0 : (float)$payment->loan_percents_summ
                ],
                [
                    'ИдентификаторВидаНачисления' => 'Пени',
                    'Сумма' => empty($payment->loan_peni_summ) ? 0 : (float)$payment->loan_peni_summ
                ]
            ];

        self::$orderId = $payment->order_id;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);

        $response = self::send_request('CRM_WebService', 'Payments', $request);
        $result = json_decode($response);
        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($item, $result);echo '</pre><hr />';
        if (isset($result->return) && $result->return == 'OK')
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 2
            );
        }
        else
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 8
            );                    
        }
            
        OperationsORM::where('id', $payment->id)->update($update);
        
        return $result;
    }

    public static function send_service($service)
    {
        $item = new StdClass();
        $item->Дата = date('Ymd000000', strtotime($service->date));
        $item->Клиент_id = (string)$service->user_id;
        $item->Сумма = $service->insurance_cost;
        $item->НомерДоговора = (string)$service->number;
        $item->Операция_id = (string)$service->crm_operation_id;
        $item->Страховка = $service->is_insurance;
        $item->OrderID = $service->order_id;
        $item->OperationID = $service->operation_id;
        $item->НомерКарты = $service->card_pan;

        self::$orderId = $service->order_id;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);

        $response = self::send_request('CRM_WebService', 'SaleService', $request);
        $result = json_decode($response);

        if (isset($result->return) && $result->return == 'OK')
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 2
            );
        }
        else
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 8
            );                    
        }
            
        OperationsORM::where('id', $service->crm_operation_id)->update($update);
        
        return $result;
    }

    public static function send_refund_service($service)
    {
        $item = new StdClass();
        $item->Дата = date('Ymd000000', strtotime($service->date));
        $item->Сумма = $service->amount;
        $item->ID_Займ = $service->contract_id;
        $item->ID_Оплата = $service->id;
        $item->id = $service->id;
        $item->СуммаОД = $service->loan_body_summ;
        $item->СуммаПроцентов  = $service->loan_percents_summ;
        $item->СуммаПени = $service->loan_peni_summ;
        $item->Услуга_OperationID = $service->service_operation_id;

        $item =
            [
                [
                    'Дата' => date('Ymd000000', strtotime($service->date)),
                    'Сумма' => $service->amount,
                    'ID_Займ' => $service->contract_id,
                    'ID_Оплата' => $service->id,
                    'id' => $service->id,
                    'СуммаОД' => $service->loan_body_summ,
                    'СуммаПроцентов' => $service->loan_percents_summ,
                    'СуммаПени' => $service->loan_peni_summ,
                    'Услуга_OperationID' => $service->service_operation_id,
                ]
            ];


        self::$orderId = $service->order_id;

        $request = new StdClass();
        $request->TextJSON = json_encode($item);


        $response = self::send_request('CRM_WebService', 'CreditingServicesForPayment', $request);
        $result = json_decode($response);
        var_dump(isset($result->return), $result->return);

        if (isset($result->return) && $result->return == 'ОК')
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 2
            );
        }
        else
        {
            $update = array(
                'sent_date' => date('Y-m-d H:i:s'),
                'sent_status' => 8
            );                    
        }
            
        OperationsORM::where('id', $service->id)->update($update);
        
        // var_dump($result);
        return $result;
    }


}