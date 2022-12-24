<?php

class Idx_scoring extends Core
{
    public function run_scoring($scoring_id)
    {
        $scoring = $this->scorings->get_scoring($scoring_id);
        $order = $this->orders->get_order((int)$scoring->order_id);

        if (empty($order)) {
            $update =
                [
                    'status' => 'error',
                    'string_result' => 'Не найдена заявка'
                ];
        } elseif (empty($order->lastname)) {
            $update =
                [
                    'status' => 'error',
                    'string_result' => 'в заявке не указана фамилия'
                ];
        } elseif (empty($order->firstname)) {
            $update =
                [
                    'status' => 'error',
                    'string_result' => 'в заявке не указано имя'
                ];
        } elseif (empty($order->phone_mobile)) {
            $update =
                [
                    'status' => 'error',
                    'string_result' => 'в заявке не указан телефон'
                ];
        } else {

            $person =
                [
                    'personLastName' => $order->lastname,
                    'personFirstName' => $order->firstname,
                    'phone' => $order->phone_mobile
                ];

            if (!empty($order->birth))
                $person['personBirthDate'] = date('d.m.Y', strtotime($order->birth));

            if (!empty($order->phone_mobile))
                $person['personMidName'] = preg_replace('/[^0-9]/', '', $order->phone_mobile);

            $score = $this->IdxApi->search($person);


            $update =
                [
                    'status' => 'completed',
                    'body' => '',
                    'success' => empty($score) ? 0 : 1
                ];

            if (!empty($score))
            {
                $update['string_result'] = 'Пользователь найден: '. $this->IdxApi->result[$score['validationScorePhone']];
                $update['body'] = $score['validationScorePhone'];
            }
            else
                $update['string_result'] = 'Клиент не найден в списке';
        }

        $this->scorings->update_scoring($scoring_id, $update);

        return $update;
    }
}