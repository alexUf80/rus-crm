<?php

ini_set("soap.wsdl_cache_enabled", 0);
ini_set('default_socket_timeout', '300');

class Onec implements ApiInterface
{
    protected static $link = "https://1c.redlab.su/corp_ruszaymserv/ru_RU/";
    protected static $login = 'ruszaym\microusr05';
    protected static $password = 'w9D#GbUB9u';
    protected static $orderId;

    public static function sendRequest($orderId)
    {
        self::send_loan($orderId);
    }

    private static function send_loan($order_id)
    {
        self::$orderId = $order_id;

        $order = OrdersORM::find($order_id);
        $contract = ContractsORM::find($order->contract_id);
        $user = UsersORM::find($order->user_id);

        $user->regaddress = AdressesORM::find($user->regaddress_id);
        $user->faktaddress = AdressesORM::find($user->faktaddress_id);

        $passport_serial = str_replace([' ', '-'], '', $user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);

        $item = new StdClass();

        $item->ID = (string)$contract->id;
        $item->НомерДоговора = $contract->number;
        $item->Дата = date('Ymd000000', strtotime($contract->inssuance_date));
        $item->Срок = $contract->period;
        $item->Периодичность = 'День';
        $item->ПроцентнаяСтавка = $contract->base_percent;
        $item->ПСК = '365';
        $item->ПДН = '0';
        $item->УИДСделки = $contract->number;
        $item->ИдентификаторФормыВыдачи = 'Безналичная';
        $item->ИдентификаторФормыОплаты = 'ТретьеЛицо';
        $item->Сумма = $contract->amount;
        $item->Порог = '1.5';
        $item->ИННОрганизации = '9725055162';
        $item->СпособПодачиЗаявления = 'Прямой';

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

        $insert =
            [
                'orderId' => self::$orderId,
                'request' => json_encode(json_decode($request->TextJSON), JSON_UNESCAPED_UNICODE),
                'response' => json_encode($response, JSON_UNESCAPED_UNICODE)
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
}