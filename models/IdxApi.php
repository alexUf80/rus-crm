<?php

/*
   Возвращает:

   Параметр	            Тип	    Обязательный	Описание
   resultCode	        Int	    Да	            Результат выполнения функции (0 – успешное завершение функции, отличное от 0 значение – ошибка выполнения)
   operationToken	    String	Нет	            Уникальный идентификатор операции
   operationResult	    String	Нет	            Результат операции
   validationScorePhone	Int	    Нет	            Оценка актуальности телефонного номера, возможные значения см. ниже

   5	Высокий уровень соответствия, подтверждение в период 180+ дней
   4	Средний уровень соответствия, подтверждение в период 90-180 дней, отсутствие несоответствий за последние 60 дней
   3	Низкий уровень соответствия, подтверждение в период 0-90 дней, наличие несоответствий за последние 60 дней
   2	Средний риск несоответствия, подтверждение в период 0-90 дней, наличие несоответствий за последние 60 дней
   1	Высокий риск несоответствия, отсутствие подтверждения в период 0+ дней и наличие несоответствий за последние 60 дней
   0	Нет данных

 */

class IdxApi extends Core
{
    protected $accessKey = 'barents-finans-4754e180843f443f3ea7c22329edf986c382cac8';
    protected $secretKey = 'a42855fd62f5b3c0778b5809149a1ee07c9d2838';

    public $result =
        [
            5 => 'Высокий уровень соответствия, подтверждение в период 180+ дней',
            4 => 'Средний уровень соответствия, подтверждение в период 90-180 дней, отсутствие несоответствий за последние 60 дней',
            3 => 'Низкий уровень соответствия, подтверждение в период 0-90 дней, наличие несоответствий за последние 60 дней',
            2 => 'Средний риск несоответствия, подтверждение в период 0-90 дней, наличие несоответствий за последние 60 дней',
            1 => 'Высокий риск несоответствия, отсутствие подтверждения в период 0+ дней и наличие несоответствий за последние 60 дней',
            0 => 'Нет данных'
        ];

    public function search($person)
    {
        $lastname   = $person['personLastName'];
        $firstname  = $person['personFirstName'];
        $patronymic = $person['personMidName'];
        $birth      = $person['personBirthDate'];
        $phone      = $person['phone'];

        $person =
            [
                'accessKey' => $this->accessKey,
                'secretKey' => $this->secretKey,
                'personLastName' => $lastname,
                'personFirstName' => $firstname,
                'phone' => $phone
            ];

        if (!empty($birth))
            $person['personBirthDate'] = date('d.m.Y', strtotime($birth));

        if (!empty($patronymic))
            $person['personMidName'] = $patronymic;

        return $this->send_request($person);
    }

    private function send_request($params)
    {
        $headers =
            [
                'Content-Type: application/json',
                'Accept: application/json'
            ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.id-x.org/idx/api2/verifyPhone',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_CUSTOMREQUEST => 'POST'
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response, true);

        return $response;
    }
}