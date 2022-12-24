<?php

class Onec implements ToolsInterface
{
    protected static $params;

    public static function request($status)
    {

        $orders = OrdersORM::with('user')->where('status', $status)->get();

        $i = 0;

        foreach ($orders as $order) {
            list($passportSerial, $passportNumber) = explode('-', $order->user->passport_serial);

            $xml['Справочники'][$i]['Контрагент'] =
                [
                    'Наименование' => trim($order->user->lastname . ' ' . $order->user->firstname . ' ' . $order->user->patronymic),
                    'УИД' => trim($order->user->id),
                    "ВидКонтрагента" => 'ФизЛицо',
                    'СерияПаспорта' => trim($passportSerial),
                    'НомерПаспорта' => trim($passportNumber),
                    'ДатаРождения' => date('Y-m-d', strtotime($order->user->birth)),
                    'КемВыданПаспорт' => trim($order->user->passport_issued . ' ' . date('Y-m-d', strtotime($order->user->passport_date))),
                    'КодПодразделения' => trim($order->user->subdivision_code),
                    'МестоРождения' => trim($order->user->birth_place),
                    'Пол' => ($order->user->gender == 'female') ? 'Ж' : 'М',
                    'СотовыйТелефон' => trim($order->user->phone_mobile),
                    'Фамилия' => trim($order->user->lastname),
                    'Имя' => trim($order->user->firstname),
                    'Отчество' => trim($order->user->patronymic),
                    'ИндексПоРегистрации' => trim($order->user->regAddress->zip),
                    'ИндексФактическогоПроживания' => trim($order->user->factAddress->zip),
                    'РайонОбластьПоРегистрации' => trim($order->user->regAddress->region),
                    'РайонОбластьФактическогоПроживания' => trim($order->user->factAddress->region),
                    'ГородПоРегистрации' => trim($order->user->regAddress->city . ' ' . $order->user->regAddress->city_type),
                    'ГородФактическогоПроживания' => trim($order->user->factAddress->city . ' ' . $order->user->factAddress->city_type),
                    'УлицаПоРегистрации' => trim($order->user->regAddress->street . ' ' . $order->user->regAddress->street_type),
                    'УлицаФактическогоПроживания' => trim($order->user->factAddress->street . ' ' . $order->user->factAddress->street_type),
                    'ДомПоРегистрации' => trim($order->user->regAddress->building),
                    'ДомФактическогоПроживания' => trim($order->user->factAddress->building),
                    'КвартираПоРегистрации' => trim($order->user->regAddress->room),
                    'КвартираФактическогоПроживания' => trim($order->user->factAddress->room),
                    'ПредставлениеАдресаПоРегистрации' => trim($order->user->regAddress->adressfull),
                    'ПредставлениеАдресаФактическогоПроживания' => trim($order->user->factAddress->adressfull),
                    'МестоРаботы' => trim($order->user->workplace),
                    'РабочийТелефон' => trim($order->user->workphone),
                    'Email' => trim($order->user->email),
                    'ДатаСоздания' => date('Y-m-d', strtotime($order->user->created))
                ];

            $i++;
        }

        $xml['Справочники']['Подразделение'] = ['Наименование' => 'АРХАНГЕЛЬСК 1', 'УИД' => 1];
        $xml['Справочники']['Организация'] = ['Наименование' => 'ООО МКК "БАРЕНЦ ФИНАНС"', 'УИД' => 1];

        $xml['Справочники'][$i]['КредитныеПродукты'] =
            [
                'Наименование' => 'Стандартный',
                'УИД' => 1,
                'Процент' => 1
            ];

        $i++;

        $promocodes = PromocodesORM::get();

        foreach ($promocodes as $promocode) {
            $percent = 1 - ($promocode->discount / 100);

            $xml['Справочники'][$i]['КредитныеПродукты'] =
                [
                    'Наименование' => 'Стандартный-' . $promocode->id,
                    'УИД' => $promocode->id,
                    'Процент' => $percent
                ];
            $i++;
        }

        foreach ($orders as $order) {
            $contract = ContractsORM::where('order_id', $order->id)->first();

            $xml['Документы'][$i]['Сделка'] =
                [
                    'ДатаЗайма' => date('Y-m-d', strtotime($order->date)),
                    'НомерЗайма' => $contract->number,
                    'УИД' => $contract->id,
                    'ПСК' => number_format(round($contract->base_percent * 365, 3), 3, '.', ''),
                    'Организация' => 1,
                    'Подразделение' => 1,
                    'СуммаЗайма' => number_format(round($contract->amount, 2), 2, '.', ''),
                    'ДатаВозврата' => date('Y-m-d', strtotime($contract->return_date)),
                    'Заемщик' => $order->user->id,
                    'ТипДокументаРасхода' => 0,
                    'ДатаРасхода' => date('Y-m-d', strtotime($contract->inssuance_date)),
                ];

            $issuanceOperation = OperationsORM::where('contract_id', $contract->id)->where('type', 'P2P')->first();

            $xml['Документы'][$i]['Сделка']['НомерДокументаРасхода'] = $issuanceOperation->id;

            $promocodes = PromocodesORM::get();

            $product = ['КредитныйПродукт' => 1];

            foreach ($promocodes as $promocode) {

                $percent = 1 - ($promocode->discount / 100);

                if ($percent == $contract->base_percent)
                    $product = ['КредитныйПродукт' => $promocode->id];
            }

            $xml['Документы'][$i]['Сделка'] = array_slice($xml['Документы'][$i]['Сделка'], 0, 4, true) +
                $product +
                array_slice($xml['Документы'][$i]['Сделка'], 4, count($xml['Документы'][$i]['Сделка'])-4, true);

            $operations = OperationsORM::where('contract_id', $contract->id)->where('type', 'PAY')->get();

            $k = 0;

            foreach ($operations as $operation) {
                $transaction = TransactionsORM::find($operation->transaction_id);

                $xml['Документы'][$i]['Сделка'][$k]['Оплаты'] =
                    [
                        'НомерПриходника' => $operation->id,
                        'ДатаОплаты' => date('Y-m-d', strtotime($operation->created)),
                        'СуммаОплаты' => number_format(round($operation->amount, 2), 2, '.', ''),
                        'ТипДокумента' => 0,
                        'Подразделение' => 1,
                        'СуммаПроцентовОплаченных' => ($transaction->loan_percents_summ != null) ? $transaction->loan_percents_summ : 0,
                        'СуммаШтрафовОплаченных' => ($transaction->loan_peni_summ != null) ? $transaction->loan_peni_summ : 0,
                        'СуммаОсновногоДолга' => ($transaction->loan_body_summ != null) ? $transaction->loan_body_summ : 0
                    ];

                $k++;
            }

            $i++;
        }

        return self::processing($xml);
    }

    public static function processing($xml)
    {
        $xmlSerializer = new XMLSerializer("Выгрузка xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns='http://localhost/mfo'", 'Выгрузка');
        $xml = $xmlSerializer->serialize($xml);
        self::$params = $xml;

        return self::response($xml);
    }

    public static function response($resp)
    {
        self::toLogs($resp);

        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="client.xml"');

        echo $resp;
    }

    public static function toLogs($log)
    {
        $insert =
            [
                'className' => self::class,
                'log' => $log
            ];

        LogsORM::insert($insert);
    }
}