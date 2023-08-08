<?php

class Fssp_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        $order = $this->orders->get_order($scoring->order_id);

        $params =
            [
                'UserID' => 'eco-zaim',
                'Password' => 'b=sFNxC2',
                'sources' => 'fssp',
                'PersonReq' => [
                    'first' => $order->firstname,
                    'middle' => $order->patronymic,
                    'paternal' => $order->lastname,
                    'birthDt' => date('Y-m-d', strtotime($order->birth))
                ]
            ];

        $request = $this->send_request($params);
        if (empty($request) || isset($request['Source']['Error'])) {
            $update = [
                'status' => 'error',
                'success' => 0,
                'body' => null,
                'string_result' => $request['Source']['Error']
            ];
            $this->scorings->update_scoring($scoring_id, $update);
            return $update;
        } else
            $update = ['status' => 'completed'];

        $update['body'] = json_encode($request, JSON_UNESCAPED_UNICODE);

        $expSum = 0;
        $badArticle = [];

        if ($request['Source']['ResultsCount'] > 0) {
            if (isset($request['Source']['Record'])) {
                foreach ($request['Source']['Record'] as $sources) {

                    if (isset($sources['Field'])) {
                        foreach ($sources['Field'] as $source) {
                            if ($source['FieldName'] == 'Total')
                                $expSum += $source['FieldValue'];

                            if ($source['FieldName'] == 'CloseReason1' && in_array($source['FieldValue'], [46, 47]))
                                $badArticle[] = $source['FieldValue'];
                        }
                    } else {
                        foreach ($sources as $source) {
                            if ($source['FieldName'] == 'Total')
                                $expSum += $source['FieldValue'];

                            if ($source['FieldName'] == 'CloseReason1' && in_array($source['FieldValue'], [46, 47]))
                                $badArticle[] = $source['FieldValue'];
                        }
                    }
                }
            }

            $maxExp = $this->scorings->get_type(3);
            $maxExp = $maxExp->params;
            $maxExp = $maxExp['amount'];

            if ($expSum > 0)
                $update['string_result'] = 'Сумма долга: ' . $expSum;
            else
                $update['string_result'] = 'Долгов нет';

            if ($expSum > $maxExp || !empty($badArticle)) {

                if (!empty($badArticle)) {
                    $articles = implode(',', array_unique($badArticle));
                    $update['string_result'] .= '<br>Обнаружены статьи: ' . $articles;
                }

                $update['success'] = 0;
            } else {
                $update['success'] = 1;
            }
        } else {
            $update['success'] = 1;
            $update['string_result'] = 'Долгов нет';
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