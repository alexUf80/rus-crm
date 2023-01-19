<?php

class Fms_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        $order = $this->orders->get_order((int)$scoring->order_id);

        list($passportSerial, $passportNumber) = explode('-', $order->passport_serial);

        $params =
            [
                'UserID' => 'barvil',
                'Password' => 'KsetM+H5',
                'sources' => 'fms',
                'PersonReq' => [
                    'passport_series' => $passportSerial,
                    'passport_number' => $passportNumber
                ]
            ];

        $request = $this->send_request($params);

        if (isset($request['Source']) && $request['Source']['ResultsCount'] > 0) {
            foreach ($request['Source']['Record'] as $source) {
                foreach ($source as $field) {
                    if ($field['FieldName'] == 'ResultCode' && $field['FieldValue'] == 'VALID') {
                        if ($field['FieldValue'] == 'VALID') {
                            $update = [
                                'status' => 'completed',
                                'success' => 1,
                                'body' => null,
                                'string_result' => 'Паспорт валидный',
                            ];
                        } else {
                            $update = [
                                'status' => 'completed',
                                'success' => 0,
                                'body' => null,
                                'string_result' => 'Паспорт просрочен',
                            ];
                        }
                    }
                }
            }
        } else {
            $update['body'] = null;
            $update['string_result'] = 'Клиент не найден';
        }

        $this->scorings->update_scoring($scoring_id, $update);

        return $update;
    }

    private function send_request($params)
    {
        $request = $this->XMLSerializer->serialize($params);

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