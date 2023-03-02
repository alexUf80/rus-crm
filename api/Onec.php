<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('default_socket_timeout', '300');

class Onec implements ApiInterface
{
    protected static $link = "http://79.137.210.6/corp_ruszaymserv/ws/";
    protected static $login = 'admin';
    protected static $password = '2020';
    protected static $orderId;

    public static function sendRequest($orderId)
    {
        return self::send_loan($orderId);
    }

    private static function send_loan($order_id)
    {
        self::$orderId = $order_id;

        $order = OrdersORM::find($order_id);
        $contract = ContractsORM::find($order->contract_id);
        $user = UsersORM::find($order->user_id);

        if (empty($contract->inssuance_date))
            return 3;

        $user->regaddress = AdressesORM::find($user->regaddress_id);
        $user->faktaddress = AdressesORM::find($user->faktaddress_id);

        $passport_serial = str_replace([' ', '-'], '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);

        if (empty($user->inn))
            self::getInn($user->id);

        $card = CardsORM::find($contract->card_id);

        if(!empty($card))
            $cardPan = $card->pan;
        else
            $cardPan = '';

        $item = new StdClass();

        $item->ID = (string)$contract->id;
        $item->НомерДоговора = $contract->number;
        $item->Дата = date('Ymd000000', strtotime($contract->inssuance_date));
        $item->Срок = $contract->period;
        $item->Периодичность = 'День';
        $item->ПроцентнаяСтавка = $contract->base_percent;
        $item->ПСК = '365';
        $item->ПДН = $user->pdn * 100;
        $item->УИДСделки = $contract->uid;
        $item->ИдентификаторФормыВыдачи = 'ТекущийСчетРасчетов';
        $item->ИдентификаторФормыОплаты = 'ТретьеЛицо';
        $item->Сумма = $contract->amount;
        $item->Порог = '1.5';
        $item->ИННОрганизации = '9717088848';
        $item->СпособПодачиЗаявления = 'Дистанционный';
        $item->НомерКарты = $cardPan;

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

        $result = self::send_request('CRM_WebService', 'Loans', $request);

        return $result;

        if (isset($result->return) && $result->return == 'OK')
            return 1;
        else
            return 2;
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

    private static function getInn($userId)
    {
        $user = UsersORM::find($userId);

        $passport_serial = str_replace([' ', '-'], '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);

        $params =
            [
                'UserID' => 'barvil',
                'Password' => 'KsetM+H5',
                'sources' => 'fns',
                'PersonReq' => [
                    'first' => $user->firstname,
                    'middle' => $user->patronymic,
                    'paternal' => $user->lastname,
                    'birthDt' => date('Y-m-d', strtotime($user->birth)),
                    'passport_series' => $passport_series,
                    'passport_number' => $passport_number
                ]
            ];

        $request = self::sendInfosphere($params);
        $inn = 0;

        foreach ($request['Source'] as $sources) {
            if ($sources['@attributes']['checktype'] == 'fns_inn') {
                foreach ($sources['Record'] as $fields) {
                    foreach ($fields as $field) {
                        if ($field['FieldName'] == 'INN')
                            $inn = $field['FieldValue'];
                    }
                }
            }
        }

        UsersORM::where('id', $user->id)->update(['inn' => $inn]);

        return 1;
    }

    private static function sendInfosphere($params)
    {
        $xml = new XMLSerializer();
        $request = $xml->serialize($params);

        $ch = curl_init('https://i-sphere.ru/2.00/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        $html = simplexml_load_string($html);
        $json = json_encode($html);
        $array = json_decode($json, TRUE);
        curl_close($ch);

        return $array;
    }
}